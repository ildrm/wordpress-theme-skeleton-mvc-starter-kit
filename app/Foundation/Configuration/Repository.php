<?php

declare(strict_types=1);

namespace WPMVC\Foundation\Configuration;

use WPMVC\Exceptions\ConfigurationException;

final class Repository {

	/** @param array<string, mixed> $items */
	public function __construct( private array $items = [] ) {
	}

	/** @param list<string> $names Configuration filenames without the .php suffix. */
	public static function fromDirectory( string $directory, array $names = [ 'app', 'assets', 'theme', 'view', 'wordpress' ] ): self {
		if ( ! is_dir( $directory ) ) {
			throw new ConfigurationException( sprintf( 'Configuration directory does not exist: %s', $directory ) );
		}

		$items = [];

		foreach ( $names as $name ) {
			if ( ! preg_match( '/\A[a-z][a-z0-9_-]*\z/D', $name ) ) {
				throw new ConfigurationException( sprintf( 'Invalid configuration filename: %s', $name ) );
			}

			$file = rtrim( $directory, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $name . '.php';

			if ( ! is_file( $file ) ) {
				throw new ConfigurationException( sprintf( 'Configuration file does not exist: %s', $file ) );
			}

			$value = require $file;

			if ( ! is_array( $value ) ) {
				throw new ConfigurationException( sprintf( 'Configuration file must return an array: %s', $file ) );
			}

			$items[ $name ] = $value;
		}

		return new self( $items );
	}

	public function get( ?string $key = null, mixed $default = null ): mixed {
		if ( $key === null || $key === '' ) {
			return $this->items;
		}

		$value = $this->items;

		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}

			$value = $value[ $segment ];
		}

		return $value;
	}

	public function has( string $key ): bool {
		$sentinel = new \stdClass();

		return $this->get( $key, $sentinel ) !== $sentinel;
	}

	public function set( string $key, mixed $value ): void {
		if ( $key === '' ) {
			throw new ConfigurationException( 'Configuration key cannot be empty.' );
		}

		$segments = explode( '.', $key );
		$target   = &$this->items;

		foreach ( $segments as $segment ) {
			if ( $segment === '' ) {
				throw new ConfigurationException( sprintf( 'Invalid configuration key: %s', $key ) );
			}

			if ( ! isset( $target[ $segment ] ) || ! is_array( $target[ $segment ] ) ) {
				$target[ $segment ] = [];
			}

			$target = &$target[ $segment ];
		}

		$target = $value;
	}

	/** @return array<string, mixed> */
	public function all(): array {
		return $this->items;
	}
}
