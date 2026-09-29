<?php

declare(strict_types=1);

namespace WPMVC\Support;

use JsonException;
use function WPMVC\Support\config;

/** Resolves a Vite build manifest without reading it more than once per request. */
final class Vite {

	/** @var array<string, array<string, mixed>>|null */
	private ?array $manifest = null;

	/** @var bool Whether the manifest has been read during this request. */
	private bool $loaded = false;

	public function __construct(
		private readonly ?string $basePath = null,
		private readonly ?string $manifestPath = null,
	) {
	}

	/**
	 * @return array{file: string, css: list<string>}|null
	 */
	public function asset( string $entry ): ?array {
		$manifest = $this->manifest();
		$record   = $manifest[ $entry ] ?? null;

		if ( ! is_array( $record ) ) {
			return null;
		}

		$file = $record['file'] ?? null;

		if ( ! is_string( $file ) || ! $this->isSafePath( $file ) ) {
			return null;
		}

		$css = [];

		$stylesheets = $record['css'] ?? [];

		if ( ! is_array( $stylesheets ) ) {
			$stylesheets = [];
		}

		foreach ( $stylesheets as $stylesheet ) {
			if ( is_string( $stylesheet ) && $this->isSafePath( $stylesheet ) ) {
				$css[] = $stylesheet;
			}
		}

		return [
			'file' => $file,
			'css'  => $css,
		];
	}

	public function url( string $file ): string {
		if ( ! $this->isSafePath( $file ) ) {
			throw new \InvalidArgumentException( 'Invalid Vite asset path.' );
		}

		$directory = (string) config( 'assets.build_directory', 'public/build' );

		if ( ! $this->isSafePath( $directory ) ) {
			throw new \InvalidArgumentException( 'Invalid Vite build directory.' );
		}

		return get_template_directory_uri() . '/' . trim( $directory, '/' ) . '/' . $file;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function manifest(): array {
		if ( $this->loaded ) {
			return $this->manifest ?? [];
		}

		$this->loaded = true;
		$basePath     = $this->basePath ?? get_template_directory();
		$relativePath = $this->manifestPath ?? (string) config( 'assets.manifest', 'public/build/.vite/manifest.json' );

		if ( ! $this->isSafePath( $relativePath ) ) {
			$this->manifest = [];
			return [];
		}

		$path = $basePath . '/' . $relativePath;

		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			$this->manifest = [];
			return [];
		}

		$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local, read-only build manifest; no remote request.

		if ( $contents === false ) {
			$this->manifest = [];
			return [];
		}

		try {
			$decoded = json_decode( $contents, true, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException ) {
			$this->manifest = [];
			return [];
		}

		if ( ! is_array( $decoded ) ) {
			$this->manifest = [];
			return [];
		}

		$records = [];

		foreach ( $decoded as $key => $value ) {
			if ( is_string( $key ) && is_array( $value ) ) {
				$records[ $key ] = $value;
			}
		}

		$this->manifest = $records;
		return $records;
	}

	private function isSafePath( string $path ): bool {
		return $path !== ''
			&& ! str_starts_with( $path, '/' )
			&& ! str_contains( $path, '..' )
			&& ! str_contains( $path, '://' )
			&& ! str_contains( $path, '\\' )
			&& (bool) preg_match( '/^[a-zA-Z0-9_\/.\-]+$/', $path );
	}
}
