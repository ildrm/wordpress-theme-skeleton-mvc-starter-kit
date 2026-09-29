<?php

declare(strict_types=1);

namespace WPMVC\Routing;

final class TemplateRouter {
	public function __construct( private readonly RouteCollection $routes ) {}

	/** @param callable|array{0: class-string|object, 1: string} $action */
	public function template( string $key, callable|array $action ): self {
		$this->routes->add( new Route( $key, $action ) );
		return $this;
	}

	public function route( string $key ): Route {
		return $this->routes->get( $key );
	}

	public function routes(): RouteCollection {
		return $this->routes;
	}
}
