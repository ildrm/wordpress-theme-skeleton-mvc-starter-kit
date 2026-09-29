<?php

declare(strict_types=1);

namespace WPMVC\View;

/**
 * Explicit template context. Views read named values through $data.
 */
final class ViewData {

	/** @param array<string, mixed> $items */
	public function __construct( private readonly array $items = [] ) {
	}

	public function get( string $key, mixed $default = null ): mixed {
		return array_key_exists( $key, $this->items ) ? $this->items[ $key ] : $default;
	}

	public function has( string $key ): bool {
		return array_key_exists( $key, $this->items );
	}

	public function with( string $key, mixed $value ): self {
		return new self(
			[
				...$this->items,
				$key => $value,
			]
		);
	}

	/** @param array<string, mixed> $items */
	public function merge( array $items ): self {
		return new self( [ ...$this->items, ...$items ] );
	}

	/** @return array<string, mixed> */
	public function all(): array {
		return $this->items;
	}

	public function __get( string $key ): mixed {
		return $this->get( $key );
	}

	public function __isset( string $key ): bool {
		return $this->has( $key );
	}
}
