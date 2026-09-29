<?php

declare(strict_types=1);

namespace WPMVC\View;

use WPMVC\Foundation\Container;

final class ViewFactory {

	/** @var array<string, list<callable>> */
	private array $composers = [];

	public function __construct(
		private readonly ViewFinder $finder,
		private readonly ?Container $container = null
	) {
	}

	/** @param array<string, mixed>|ViewData $data */
	public function make( string $name, array|ViewData $data = [] ): View {
		return new View( $this, $name, $data instanceof ViewData ? $data : new ViewData( $data ) );
	}

	/** @param array<string, mixed>|ViewData $data */
	public function render( string $name, array|ViewData $data = [] ): string {
		return $this->make( $name, $data )->render();
	}

	/** @param string|list<string> $views */
	public function composer( string|array $views, callable $composer ): void {
		foreach ( (array) $views as $name ) {
			$this->composers[ $name ][] = $composer;
		}
	}

	public function compose( string $name, ViewData $data ): ViewData {
		foreach ( $this->composers as $pattern => $callbacks ) {
			if ( ! $this->matches( $pattern, $name ) ) {
				continue;
			}

			foreach ( $callbacks as $callback ) {
				$result = $this->container !== null
					? $this->container->call(
						$callback,
						[
							'data' => $data,
							'name' => $name,
						]
					)
					: $callback( $data, $name );

				if ( $result instanceof ViewData ) {
					$data = $result;
				} elseif ( is_array( $result ) ) {
					$data = $data->merge( $result );
				}
			}
		}

		return $data;
	}

	public function renderTemplate( string $name, ViewData $data ): string {
		$file = $this->finder->find( $name );

		// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Included templates use both scoped variables.
		return ( static function ( string $file, ViewData $data, ViewFactory $view ): string {
			ob_start();

			try {
				require $file;

				return (string) ob_get_clean();
			} catch ( \Throwable $exception ) {
				ob_end_clean();
				throw $exception;
			}
		} )( $file, $data, $this );
	}

	private function matches( string $pattern, string $name ): bool {
		if ( $pattern === '*' ) {
			return true;
		}

		if ( ! str_contains( $pattern, '*' ) ) {
			return $pattern === $name;
		}

		$expression = '/\A' . str_replace( '\\*', '.*', preg_quote( $pattern, '/' ) ) . '\z/D';

		return (bool) preg_match( $expression, $name );
	}
}
