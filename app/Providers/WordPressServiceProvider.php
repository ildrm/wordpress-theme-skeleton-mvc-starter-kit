<?php

declare(strict_types=1);

namespace WPMVC\Providers;

use WPMVC\Foundation\ServiceProvider;
use WPMVC\WordPress\Hooks\HookRegistrar;

final class WordPressServiceProvider extends ServiceProvider {
	public function register(): void {}

	public function boot(): void {
		$this->app->make( HookRegistrar::class )->register();
	}
}
