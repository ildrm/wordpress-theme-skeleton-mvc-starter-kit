<?php

declare(strict_types=1);

namespace WPMVC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPMVC\Foundation\Application;
use WPMVC\Foundation\Configuration\Repository;
use WPMVC\Tests\Fixtures\FirstProvider;
use WPMVC\Tests\Fixtures\SecondProvider;
use function WPMVC\Support\app;
use function WPMVC\Support\config;

final class ApplicationTest extends TestCase {

	public function testProvidersRegisterBeforeAnyProviderBoots(): void {
		$application = new Application( dirname( __DIR__, 2 ) );
		$application->register( FirstProvider::class );
		$application->register( SecondProvider::class );

		self::assertFalse( $application->isBooted() );
		$application->boot();

		self::assertTrue( $application->isBooted() );
		self::assertSame( 'registered', $application->make( 'first_boot' ) );
		self::assertSame( dirname( __DIR__, 2 ) . '/resources/views', $application->resourcePath( 'views' ) );
	}

	public function testBoundaryHelpersResolveCurrentApplicationAndConfiguration(): void {
		$application = new Application( dirname( __DIR__, 2 ) );
		$application->setConfiguration( new Repository( [ 'theme' => [ 'name' => 'Test' ] ] ) );
		Application::setCurrent( $application );

		try {
			self::assertSame( $application, app() );
			self::assertSame( 'Test', config( 'theme.name' ) );
			self::assertSame( 'fallback', config( 'theme.missing', 'fallback' ) );
		} finally {
			Application::setCurrent( null );
		}
	}
}
