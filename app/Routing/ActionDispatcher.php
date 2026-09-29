<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use WPMVC\Foundation\Application;

/** Resolves controller actions through the theme container. */
final class ActionDispatcher {
	public function __construct( private readonly Application $app ) {}

	/** @param callable|array{0: class-string|object, 1: string}|string $action
	 *  @param array<string, mixed> $parameters
	 */
	public function call( callable|array|string $action, array $parameters = [] ): mixed {
		if ( is_array( $action ) && is_string( $action[0] ) ) {
			$action[0] = $this->app->make( $action[0] );
		}

		return $this->app->call( $action, $parameters );
	}
}
