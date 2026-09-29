<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use InvalidArgumentException;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/** WordPress REST endpoint declarations. WordPress remains the HTTP router. */
final class RestRouter {
	/** @var array<string, array{method: string, path: string, action: callable|array<int, mixed>, permission: callable|string|array<int, mixed>, args: array<string, array<string, mixed>>}> */
	private array $endpoints = [];

	public function __construct(
		private readonly ActionDispatcher $dispatcher,
		private readonly string $namespace = 'wpmvc/v1',
	) {
		if ( ! preg_match( '~^[a-z0-9_-]+(?:/[a-z0-9_-]+)+$~', $namespace ) ) {
			throw new InvalidArgumentException( 'REST namespace must contain a vendor and version.' );
		}
	}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function get( string $path, callable|array $action, array $options ): self {
		return $this->add( 'GET', $path, $action, $options );
	}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function post( string $path, callable|array $action, array $options ): self {
		return $this->add( 'POST', $path, $action, $options );
	}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function put( string $path, callable|array $action, array $options ): self {
		return $this->add( 'PUT', $path, $action, $options );
	}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function patch( string $path, callable|array $action, array $options ): self {
		return $this->add( 'PATCH', $path, $action, $options );
	}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function delete( string $path, callable|array $action, array $options ): self {
		return $this->add( 'DELETE', $path, $action, $options );
	}

	/** @param callable|array<int, mixed> $action
	 *  @param array<string, mixed> $options
	 */
	public function add( string $method, string $path, callable|array $action, array $options ): self {
		$method = strtoupper( $method );
		if ( ! in_array( $method, [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ], true ) ) {
			throw new InvalidArgumentException( 'Unsupported REST method.' );
		}

		if ( ! str_starts_with( $path, '/' ) || str_contains( $path, "\0" ) || preg_match( '/\s/', $path ) ) {
			throw new InvalidArgumentException( 'REST path must start with / and contain no whitespace.' );
		}

		$permission = $options['permission'] ?? null;
		if ( ! is_callable( $permission ) && ! ( is_string( $permission ) && '' !== $permission ) && ! ( is_array( $permission ) && count( $permission ) === 2 && isset( $permission[0], $permission[1] ) && ( is_string( $permission[0] ) || is_object( $permission[0] ) ) && is_string( $permission[1] ) ) ) {
			throw new InvalidArgumentException( 'Every REST endpoint needs an explicit permission callback or capability.' );
		}

		$args = $options['args'] ?? [];
		if ( ! is_array( $args ) ) {
			throw new InvalidArgumentException( 'REST endpoint args must be an array.' );
		}

		$validatedArgs = [];
		foreach ( $args as $name => $schema ) {
			if ( ! is_string( $name ) || ! is_array( $schema ) || ( ! isset( $schema['type'] ) && ! isset( $schema['validate_callback'] ) ) ) {
				throw new InvalidArgumentException( 'Each REST argument needs a type or validation callback.' );
			}
			if ( isset( $schema['sanitize_callback'], $schema['type'] ) && ! isset( $schema['validate_callback'] ) ) {
				// A custom sanitizer bypasses WordPress's automatic schema validation.
				$schema['validate_callback'] = 'rest_validate_request_arg';
			}

			$validatedArgs[ $name ] = $schema;
		}

		$key = $method . ' ' . $path;
		if ( isset( $this->endpoints[ $key ] ) ) {
			throw new InvalidArgumentException( sprintf( 'REST endpoint "%s" is already registered.', $key ) );
		}

		$this->endpoints[ $key ] = [
			'method'     => $method,
			'path'       => $path,
			'action'     => $action,
			'permission' => $permission,
			'args'       => $validatedArgs,
		];
		return $this;
	}

	public function hook(): void {
		add_action( 'rest_api_init', $this->register( ... ) );
	}

	public function register(): void {
		foreach ( $this->endpoints as $endpoint ) {
			$action     = $endpoint['action'];
			$permission = $endpoint['permission'];
			register_rest_route(
				$this->namespace,
				$endpoint['path'],
				[
					'methods'             => $endpoint['method'],
					'callback'            => fn ( WP_REST_Request $request ) => $this->invoke( $action, $request ),
					'permission_callback' => fn ( WP_REST_Request $request ) => $this->authorize( $permission, $request ),
					'args'                => $endpoint['args'],
				]
			);
		}
	}

	/** @param callable|string|array<int, mixed> $permission */
	private function authorize( callable|string|array $permission, WP_REST_Request $request ): bool|WP_Error {
		if ( is_string( $permission ) && ! is_callable( $permission ) ) {
			return current_user_can( $permission );
		}

		$result = $this->dispatcher->call( $permission, [ 'request' => $request ] );
		return $result instanceof WP_Error ? $result : (bool) $result;
	}

	/** @param callable|array<int, mixed> $action */
	private function invoke( callable|array $action, WP_REST_Request $request ): WP_REST_Response|WP_Error {
		try {
			$result = $this->dispatcher->call( $action, [ 'request' => $request ] );
			return $result instanceof WP_Error ? $result : rest_ensure_response( $result );
		} catch ( Throwable $exception ) {
			$message = defined( 'WP_DEBUG' ) && WP_DEBUG ? $exception->getMessage() : 'Internal server error.';
			return new WP_Error( 'wpmvc_rest_error', $message, [ 'status' => 500 ] );
		}
	}
}
