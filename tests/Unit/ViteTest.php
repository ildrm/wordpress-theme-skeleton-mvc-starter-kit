<?php

declare(strict_types=1);

namespace WPMVC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPMVC\Support\Vite;

final class ViteTest extends TestCase {
	// phpcs:disable WordPress.WP.AlternativeFunctions -- These isolated unit tests run without WordPress loaded.

	/** @var string Temporary directory containing only this test's manifest fixture. */
	private string $directory;

	protected function setUp(): void {
		$this->directory = sys_get_temp_dir() . '/wpmvc-vite-' . bin2hex( random_bytes( 8 ) );
		mkdir( $this->directory, 0700, true );
	}

	protected function tearDown(): void {
		$manifest = $this->directory . '/manifest.json';

		if ( is_file( $manifest ) ) {
			unlink( $manifest );
		}

		rmdir( $this->directory );
	}

	public function testResolvesEntryAndCachesManifestWithinRequest(): void {
		file_put_contents(
			$this->directory . '/manifest.json',
			json_encode(
				[
					'resources/js/app.js' => [
						'file' => 'assets/app-123.js',
						'css'  => [ 'assets/app-123.css' ],
					],
				],
				JSON_THROW_ON_ERROR
			)
		);

		$vite     = new Vite( $this->directory, 'manifest.json' );
		$expected = [
			'file' => 'assets/app-123.js',
			'css'  => [ 'assets/app-123.css' ],
		];

		self::assertSame( $expected, $vite->asset( 'resources/js/app.js' ) );

		file_put_contents( $this->directory . '/manifest.json', '{}' );

		self::assertSame( $expected, $vite->asset( 'resources/js/app.js' ) );
		self::assertNull( $vite->asset( 'resources/js/missing.js' ) );
	}

	public function testRejectsUnsafeBuiltPaths(): void {
		file_put_contents(
			$this->directory . '/manifest.json',
			json_encode(
				[
					'resources/js/app.js' => [
						'file' => '../outside.js',
						'css'  => [ 'https://example.invalid/style.css' ],
					],
				],
				JSON_THROW_ON_ERROR
			)
		);

		self::assertNull( ( new Vite( $this->directory, 'manifest.json' ) )->asset( 'resources/js/app.js' ) );
	}

	public function testReturnsNullForMissingOrMalformedManifest(): void {
		$vite = new Vite( $this->directory, 'manifest.json' );
		self::assertNull( $vite->asset( 'resources/js/app.js' ) );

		file_put_contents( $this->directory . '/manifest.json', '{bad json' );
		self::assertNull( ( new Vite( $this->directory, 'manifest.json' ) )->asset( 'resources/js/app.js' ) );
	}
}
