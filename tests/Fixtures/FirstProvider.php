<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

use WPMVC\Foundation\ServiceProvider;

final class FirstProvider extends ServiceProvider {
	public function register(): void {
		$this->app->instance( 'first', 'ready' );
	}

	public function boot(): void {
		$this->app->instance( 'first_boot', $this->app->make( 'second' ) );
	}
}
