<?php

declare(strict_types=1);

namespace WPMVC\Http\Requests;

/** A small input boundary for classic and admin-ajax requests. */
final class Request {
	/** @param array<string, mixed> $query
	 *  @param array<string, mixed> $body
	 *  @param array<string, mixed> $files
	 */
	public function __construct(
		private readonly string $method,
		private readonly array $query = [],
		private readonly array $body = [],
		private readonly array $files = [],
	) {}

	public static function fromGlobals(): self {
		return new self(
			strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ),
			wp_unslash( $_GET ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading only; individual handlers verify nonces when required.
			wp_unslash( $_POST ), // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked by AjaxRouter before dispatch.
			$_FILES, // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Files are exposed for explicit validation by handlers.
		);
	}

	public function method(): string {
		return $this->method;
	}

	public function input( string $key, mixed $default = null ): mixed {
		return $this->body[ $key ] ?? $this->query[ $key ] ?? $default;
	}

	public function query( string $key, mixed $default = null ): mixed {
		return $this->query[ $key ] ?? $default;
	}

	public function body( string $key, mixed $default = null ): mixed {
		return $this->body[ $key ] ?? $default;
	}

	public function file( string $key ): mixed {
		return $this->files[ $key ] ?? null;
	}

	/** @return array<string, mixed> */
	public function all(): array {
		return array_merge( $this->query, $this->body );
	}

	public function withInput( string $key, mixed $value ): self {
		$body         = $this->body;
		$body[ $key ] = $value;
		return new self( $this->method, $this->query, $body, $this->files );
	}
}
