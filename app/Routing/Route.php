<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use InvalidArgumentException;

/** A named WordPress template context and its controller action. */
final class Route {
	/** @var callable|array<int, mixed> */
	private readonly mixed $action;

	/** @param callable|array<int, mixed> $action */
	public function __construct(
		private readonly string $key,
		callable|array $action,
	) {
		if ( ! preg_match( '/^[a-z0-9][a-z0-9_-]*$/', $key ) ) {
			throw new InvalidArgumentException( 'Invalid template route key.' );
		}

		if ( is_array( $action ) && ( count( $action ) !== 2 || ! isset( $action[0], $action[1] ) || ! ( is_string( $action[0] ) || is_object( $action[0] ) ) || ! is_string( $action[1] ) ) ) {
			throw new InvalidArgumentException( 'A template route action must be callable or a [class, method] pair.' );
		}
		$this->action = $action;
	}

	public function key(): string {
		return $this->key;
	}

	/** @return callable|array<int, mixed> */
	public function action(): mixed {
		return $this->action;
	}
}
