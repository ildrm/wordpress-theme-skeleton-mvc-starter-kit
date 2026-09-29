<?php
declare(strict_types=1);

namespace WPMVC\Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

final class BoundaryTest extends TestCase {

	private const ROOT_TEMPLATES = [
		'index',
		'front-page',
		'home',
		'single',
		'singular',
		'page',
		'archive',
		'author',
		'category',
		'tag',
		'taxonomy',
		'date',
		'attachment',
		'search',
		'404',
	];

	public function testWordPressRootTemplatesRemainThinAdapters(): void {
		foreach ( self::ROOT_TEMPLATES as $name ) {
			$path = dirname( __DIR__, 2 ) . '/' . $name . '.php';
			self::assertFileExists( $path );
			$code = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.
			self::assertLessThanOrEqual( 30, count( explode( "\n", $code ) ), $name . ' has grown beyond an adapter' );
			self::assertStringContainsString( "'{$name}'", $code );
			self::assertDoesNotMatchRegularExpression( '/\b(?:WP_Query|get_posts|get_terms|query_posts|\$wpdb)\b/', $code );
		}
	}

	public function testViewsDoNotQueryOrResolveServices(): void {
		foreach ( $this->phpFiles( 'resources/views' ) as $path => $code ) {
			self::assertDoesNotMatchRegularExpression(
				'/\b(?:WP_Query|get_posts|get_terms|query_posts|\$wpdb)\b|\bapp\s*\(/',
				$code,
				$path
			);
		}
	}

	public function testDependencyDirectionAndExplicitTemplateData(): void {
		foreach ( [ 'app/Repositories', 'app/Actions', 'app/Services', 'app/Models' ] as $directory ) {
			foreach ( $this->phpFiles( $directory ) as $path => $code ) {
				self::assertDoesNotMatchRegularExpression( '/\buse\s+WPMVC\\\\Http\\\\Controllers\\\\/', $code, $path );
			}
		}

		foreach ( [ 'app', 'resources/views' ] as $directory ) {
			foreach ( $this->phpFiles( $directory ) as $path => $code ) {
				self::assertDoesNotMatchRegularExpression( '/\bextract\s*\(/', $code, $path );
			}
		}
	}

	/** @return iterable<string, string> */
	private function phpFiles( string $directory ): iterable {
		$root = dirname( __DIR__, 2 ) . '/' . $directory;
		if ( ! is_dir( $root ) ) {
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->getExtension() === 'php' ) {
				yield $file->getPathname() => (string) file_get_contents( $file->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local source inspection.
			}
		}
	}
}
