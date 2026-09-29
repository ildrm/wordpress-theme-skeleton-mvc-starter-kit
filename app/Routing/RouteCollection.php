<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use InvalidArgumentException;
use OutOfBoundsException;

final class RouteCollection {
	/** @var array<string, Route> */
	private array $routes = [];

	public function add( Route $route ): void {
		if ( isset( $this->routes[ $route->key() ] ) ) {
			throw new InvalidArgumentException( sprintf( 'Template route "%s" is already registered.', $route->key() ) );
		}

		$this->routes[ $route->key() ] = $route;
	}

	public function has( string $key ): bool {
		return isset( $this->routes[ $key ] );
	}

	public function get( string $key ): Route {
		if ( ! $this->has( $key ) ) {
			throw new OutOfBoundsException( sprintf( 'No template route is registered for "%s".', $key ) );
		}

		return $this->routes[ $key ];
	}

	/** @return array<string, Route> */
	public function all(): array {
		return $this->routes;
	}
}
