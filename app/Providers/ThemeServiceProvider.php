<?php

declare(strict_types=1);

namespace WPMVC\Providers;

use WPMVC\Foundation\ServiceProvider;
use WPMVC\WordPress\Menus\MenuRegistrar;
use WPMVC\WordPress\Theme\Assets;
use WPMVC\WordPress\Theme\ImageSizes;
use WPMVC\WordPress\Theme\Sidebars;
use WPMVC\WordPress\Theme\Supports;

final class ThemeServiceProvider extends ServiceProvider {
	public function register(): void {}

	public function boot(): void {
		foreach ( [ Supports::class, MenuRegistrar::class, Sidebars::class, ImageSizes::class, Assets::class ] as $class ) {
			$this->app->make( $class )->register();
		}
	}
}
