<?php

declare(strict_types=1);

namespace WPMVC\Providers;

use InvalidArgumentException;
use WPMVC\Foundation\ServiceProvider;
use WPMVC\View\ViewFactory;
use WPMVC\View\ViewFinder;

final class ViewServiceProvider extends ServiceProvider {
	public function register(): void {
		$this->app->singleton(
			ViewFinder::class,
			function (): ViewFinder {
				$paths = $this->app->config()->get( 'view.paths', [ $this->app->resourcePath( 'views' ) ] );
				if ( ! is_array( $paths ) ) {
					throw new InvalidArgumentException( 'view.paths must be an array.' );
				}

				return new ViewFinder( $paths );
			}
		);

		$this->app->singleton( ViewFactory::class, fn (): ViewFactory => new ViewFactory( $this->app->make( ViewFinder::class ), $this->app ) );
	}
}
