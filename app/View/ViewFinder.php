<?php

declare(strict_types=1);

namespace WPMVC\View;

use InvalidArgumentException;
use WPMVC\Exceptions\ViewNotFoundException;

final class ViewFinder {

	/** @var list<string> */
	private array $paths;

	/** @var array<string, string> */
	private array $cache = [];

	/** @param list<mixed> $paths */
	public function __construct( array $paths ) {
		$this->paths = [];

		foreach ( $paths as $path ) {
			if ( ! is_string( $path ) || $path === '' ) {
				throw new InvalidArgumentException( 'View paths must be nonempty strings.' );
			}

			$this->paths[] = $path;
		}

		if ( $this->paths === [] ) {
			throw new InvalidArgumentException( 'At least one view path is required.' );
		}
	}

	public function find( string $name ): string {
		if ( ! preg_match( '/\A[A-Za-z0-9_-]+(?:\.[A-Za-z0-9_-]+)*\z/D', $name ) ) {
			throw new InvalidArgumentException( sprintf( 'Invalid view name: %s', $name ) );
		}

		if ( isset( $this->cache[ $name ] ) ) {
			return $this->cache[ $name ];
		}

		$relative = str_replace( '.', DIRECTORY_SEPARATOR, $name ) . '.php';

		foreach ( $this->paths as $path ) {
			$root = realpath( $path );

			if ( $root === false ) {
				continue;
			}

			$file = realpath( $root . DIRECTORY_SEPARATOR . $relative );

			if ( $file !== false && str_starts_with( $file, $root . DIRECTORY_SEPARATOR ) && is_file( $file ) ) {
				$this->cache[ $name ] = $file;
				return $file;
			}
		}

		throw new ViewNotFoundException( sprintf( 'View "%s" was not found in the configured view paths.', $name ) );
	}

	/** @return list<string> */
	public function paths(): array {
		return $this->paths;
	}
}
