<?php

declare(strict_types=1);

namespace WPMVC\Providers;

use WPMVC\Contracts\Repositories\PostRepository;
use WPMVC\Foundation\ServiceProvider;
use WPMVC\Repositories\WordPress\WordPressPostRepository;

final class AppServiceProvider extends ServiceProvider {
	public function register(): void {
		$this->app->bind( PostRepository::class, WordPressPostRepository::class );
	}
}
