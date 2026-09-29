<?php

declare(strict_types=1);

namespace WPMVC\Foundation;

use InvalidArgumentException;
use WPMVC\Foundation\Configuration\Repository;

final class Application extends Container {

	/** @var ?self Current application used only at WordPress entry boundaries. */
	private static ?self $current = null;

	/** @var list<ServiceProvider> */
	private array $providers = [];

	/** @var array<class-string<ServiceProvider>, true> */
	private array $registeredProviders = [];

	/** @var bool Whether all providers have booted. */
	private bool $booted = false;

	/** @var bool Guards against recursive boot calls. */
	private bool $booting = false;

	/** @var Repository Loaded application configuration. */
	private Repository $configuration;

	public function __construct( private readonly string $basePath ) {
		$this->configuration = new Repository();
		$this->instance( self::class, $this );
		$this->instance( Container::class, $this );
		$this->instance( Repository::class, $this->configuration );
		$this->instance( 'app', $this );
		$this->instance( 'config', $this->configuration );
	}

	public static function current(): ?self {
		return self::$current;
	}

	public static function setCurrent( ?self $application ): void {
		self::$current = $application;
	}

	public function basePath( string $path = '' ): string {
		return $this->joinPath( $this->basePath, $path );
	}

	public function path( string $path = '' ): string {
		return $this->basePath( $this->joinPath( 'app', $path ) );
	}

	public function themePath( string $path = '' ): string {
		return $this->basePath( $path );
	}

	public function resourcePath( string $path = '' ): string {
		return $this->basePath( $this->joinPath( 'resources', $path ) );
	}

	public function publicPath( string $path = '' ): string {
		return $this->basePath( $this->joinPath( 'public', $path ) );
	}

	public function configPath( string $path = '' ): string {
		return $this->basePath( $this->joinPath( 'config', $path ) );
	}

	public function config(): Repository {
		return $this->configuration;
	}

	public function loadConfiguration(): void {
		$this->setConfiguration( Repository::fromDirectory( $this->configPath() ) );
	}

	public function setConfiguration( Repository $configuration ): void {
		$this->configuration = $configuration;
		$this->instance( Repository::class, $configuration );
		$this->instance( 'config', $configuration );
	}

	/** @param class-string<ServiceProvider>|ServiceProvider $provider */
	public function register( string|ServiceProvider $provider ): ServiceProvider {
		if ( is_string( $provider ) ) {
			if ( ! is_subclass_of( $provider, ServiceProvider::class ) ) {
				throw new InvalidArgumentException( sprintf( '%s must extend %s.', $provider, ServiceProvider::class ) );
			}

			if ( isset( $this->registeredProviders[ $provider ] ) ) {
				foreach ( $this->providers as $registered ) {
					if ( $registered instanceof $provider ) {
						return $registered;
					}
				}
			}

			$provider = $this->make( $provider );
		}

		$class = $provider::class;

		if ( isset( $this->registeredProviders[ $class ] ) ) {
			foreach ( $this->providers as $registered ) {
				if ( $registered::class === $class ) {
					return $registered;
				}
			}
		}

		$provider->register();
		$this->providers[]                   = $provider;
		$this->registeredProviders[ $class ] = true;

		if ( $this->booted ) {
			$provider->boot();
		}

		return $provider;
	}

	public function boot(): void {
		if ( $this->booted || $this->booting ) {
			return;
		}

		$this->booting = true;

		try {
			$total = count( $this->providers );

			for ( $index = 0; $index < $total; $index++ ) {
				$this->providers[ $index ]->boot();
				$total = count( $this->providers );
			}

			$this->booted = true;
		} finally {
			$this->booting = false;
		}
	}

	public function isBooted(): bool {
		return $this->booted;
	}

	private function joinPath( string $base, string $path ): string {
		$base = rtrim( $base, '/\\' );
		$path = trim( $path, '/\\' );

		return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . $path;
	}
}
