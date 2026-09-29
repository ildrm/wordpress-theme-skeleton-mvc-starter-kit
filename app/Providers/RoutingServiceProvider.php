<?php

declare(strict_types=1);

namespace WPMVC\Providers;

use InvalidArgumentException;
use WPMVC\Foundation\ServiceProvider;
use WPMVC\Routing\ActionDispatcher;
use WPMVC\Routing\AdminRouter;
use WPMVC\Routing\AjaxRouter;
use WPMVC\Routing\RestRouter;
use WPMVC\Routing\RouteCollection;
use WPMVC\Routing\TemplateDispatcher;
use WPMVC\Routing\TemplateRouter;

final class RoutingServiceProvider extends ServiceProvider {
	public function register(): void {
		$this->app->singleton( RouteCollection::class );
		$this->app->singleton( TemplateRouter::class );
		$this->app->singleton( TemplateDispatcher::class );
		$this->app->singleton( ActionDispatcher::class );
		$this->app->singleton( AjaxRouter::class );
		$this->app->singleton( AdminRouter::class );
		$this->app->singleton(
			RestRouter::class,
			function (): RestRouter {
				$namespace = $this->app->config()->get( 'WordPress.rest_namespace', 'wpmvc/v1' );
				if ( ! is_string( $namespace ) ) {
					throw new InvalidArgumentException( 'WordPress.rest_namespace must be a string.' );
				}

				return new RestRouter( $this->app->make( ActionDispatcher::class ), $namespace );
			}
		);
	}

	public function boot(): void {
		$web = $this->app->make( TemplateRouter::class );
		$this->load( 'web', $web );

		$api = $this->app->make( RestRouter::class );
		$this->load( 'api', $api );
		$api->hook();

		$ajax = $this->app->make( AjaxRouter::class );
		$this->load( 'ajax', $ajax );
		$ajax->register();

		$admin = $this->app->make( AdminRouter::class );
		$this->load( 'admin', $admin );
		$admin->hook();
	}

	private function load( string $name, object $router ): void {
		$file = $this->app->basePath( 'routes/' . $name . '.php' );
		if ( ! is_file( $file ) ) {
			throw new InvalidArgumentException( sprintf( 'Missing route file: routes/%s.php.', $name ) );
		}

		require $file;
	}
}
