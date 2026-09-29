<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

use WPMVC\Foundation\ServiceProvider;

final class SecondProvider extends ServiceProvider {
	public function register(): void {
		$this->app->instance( 'second', 'registered' );
	}
}
