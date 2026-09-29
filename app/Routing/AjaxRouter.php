<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use InvalidArgumentException;
use Throwable;
use WPMVC\Http\Requests\Request;

/** Defines nonce-protected WordPress admin-ajax actions. */
final class AjaxRouter {
	/** @var array<string, array{action: callable|array<int, mixed>, nonce_action: string, capability: ?string, public: bool, args: array<string, array{validate: callable, sanitize: ?callable, required: bool}>}> */
	private array $endpoints = [];

	public function __construct( private readonly ActionDispatcher $dispatcher ) {}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function post( string $name, callable|array $action, array $options ): self {
		if ( ! preg_match( '/^[a-z][a-z0-9_]*$/', $name ) ) {
			throw new InvalidArgumentException( 'AJAX action names may contain only lowercase letters, digits, and underscores.' );
		}

		if ( isset( $this->endpoints[ $name ] ) ) {
			throw new InvalidArgumentException( sprintf( 'AJAX action "%s" is already registered.', $name ) );
		}

		$nonceAction = $options['nonce_action'] ?? '';
		$public      = $options['public'] ?? false;
		$capability  = $options['capability'] ?? null;
		$args        = $options['args'] ?? [];
		if ( ! is_string( $nonceAction ) || '' === $nonceAction || ! is_bool( $public ) || ! is_array( $args ) ) {
			throw new InvalidArgumentException( 'AJAX routes require a nonce action and valid argument schema.' );
		}

		if ( null !== $capability && ( ! is_string( $capability ) || '' === $capability ) ) {
			throw new InvalidArgumentException( 'AJAX capability must be a nonempty string.' );
		}

		if ( ! $public && null === $capability ) {
			throw new InvalidArgumentException( 'Private AJAX routes require a WordPress capability.' );
		}

		$validatedArgs = [];
		foreach ( $args as $key => $rule ) {
			if ( ! is_string( $key ) || ! is_array( $rule ) || ! isset( $rule['validate'] ) || ! is_callable( $rule['validate'] ) ) {
				throw new InvalidArgumentException( 'Every AJAX argument needs a validation callback.' );
			}

			$sanitize = $rule['sanitize'] ?? null;
			$required = $rule['required'] ?? false;
			if ( ( null !== $sanitize && ! is_callable( $sanitize ) ) || ! is_bool( $required ) ) {
				throw new InvalidArgumentException( 'AJAX argument sanitizer and required flag are invalid.' );
			}

			$validatedArgs[ $key ] = [
				'validate' => $rule['validate'],
				'sanitize' => $sanitize,
				'required' => $required,
			];
		}

		$this->endpoints[ $name ] = [
			'action'       => $action,
			'nonce_action' => $nonceAction,
			'capability'   => $capability,
			'public'       => $public,
			'args'         => $validatedArgs,
		];
		return $this;
	}

	public function register(): void {
		foreach ( $this->endpoints as $name => $endpoint ) {
			$handler = fn () => $this->dispatch( $endpoint );
			add_action( 'wp_ajax_' . $name, $handler );
			if ( $endpoint['public'] ) {
				add_action( 'wp_ajax_nopriv_' . $name, $handler );
			}
		}
	}

	/** @param array{action: callable|array<int, mixed>, nonce_action: string, capability: ?string, public: bool, args: array<string, array{validate: callable, sanitize: ?callable, required: bool}>} $endpoint */
	private function dispatch( array $endpoint ): void {
		if ( strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) !== 'POST' ) {
			wp_send_json_error(
				[
					'code'    => 'method_not_allowed',
					'message' => 'POST required.',
				],
				405
			);
		}

		$nonce = isset( $_POST['_ajax_nonce'] ) && is_string( $_POST['_ajax_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) )
			: '';
		if ( ! wp_verify_nonce( $nonce, $endpoint['nonce_action'] ) ) {
			wp_send_json_error(
				[
					'code'    => 'invalid_nonce',
					'message' => 'Invalid request nonce.',
				],
				403
			);
		}

		if ( null !== $endpoint['capability'] && ! current_user_can( $endpoint['capability'] ) ) {
			wp_send_json_error(
				[
					'code'    => 'forbidden',
					'message' => 'Permission denied.',
				],
				403
			);
		}

		$request = Request::fromGlobals();
		foreach ( $endpoint['args'] as $key => $rule ) {
			$value = $request->body( $key );
			if ( null === $value && $rule['required'] ) {
				wp_send_json_error(
					[
						'code'    => 'invalid_input',
						'message' => 'Required field missing.',
					],
					400
				);
			}
			if ( null === $value ) {
				continue;
			}
			if ( ! ( $rule['validate'] )( $value ) ) {
				wp_send_json_error(
					[
						'code'    => 'invalid_input',
						'message' => 'Invalid field value.',
					],
					400
				);
			}
			if ( null !== $rule['sanitize'] ) {
				$value = ( $rule['sanitize'] )( $value );
			}
			$request = $request->withInput( $key, $value );
		}

		try {
			$result = $this->dispatcher->call( $endpoint['action'], [ 'request' => $request ] );
		} catch ( Throwable $exception ) {
			$message = defined( 'WP_DEBUG' ) && WP_DEBUG ? $exception->getMessage() : 'Internal server error.';
			wp_send_json_error(
				[
					'code'    => 'server_error',
					'message' => $message,
				],
				500
			);
		}

		wp_send_json_success( $result );
	}
}
