<?php

declare(strict_types=1);

namespace WPMVC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPMVC\View\ViewData;
use WPMVC\View\ViewFactory;
use WPMVC\View\ViewFinder;

final class ViewTest extends TestCase {

	public function testDotNotationLayoutsAndComposers(): void {
		$finder  = new ViewFinder( [ dirname( __DIR__ ) . '/Fixtures/views' ] );
		$factory = new ViewFactory( $finder );
		$factory->composer( 'layouts.*', static fn (): array => [ 'site' => 'Example' ] );

		$html = $factory->make( 'pages.greeting', [ 'person' => 'Ada' ] )
			->layout( 'layouts.app' )
			->render();

		self::assertSame( '<main>Example: Hello Ada</main>', trim( $html ) );
	}

	public function testViewDataIsImmutable(): void {
		$first  = new ViewData( [ 'name' => null ] );
		$second = $first->with( 'name', 'Ada' );

		self::assertNull( $first->get( 'name', 'default' ) );
		self::assertSame( 'Ada', $second->name );
	}

	public function testFinderRejectsTraversal(): void {
		$finder = new ViewFinder( [ dirname( __DIR__ ) . '/Fixtures/views' ] );

		$this->expectException( \InvalidArgumentException::class );
		$finder->find( '../secret' );
	}
}
