<?php

declare(strict_types=1);

namespace WPMVC\Foundation;

abstract class ServiceProvider {

	public function __construct( protected readonly Application $app ) {
	}

	abstract public function register(): void;

	public function boot(): void {
	}
}
