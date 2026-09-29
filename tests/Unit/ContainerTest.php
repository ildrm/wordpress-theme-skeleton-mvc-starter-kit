<?php

declare(strict_types=1);

namespace WPMVC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPMVC\Exceptions\BindingResolutionException;
use WPMVC\Exceptions\CircularDependencyException;
use WPMVC\Foundation\Container;
use WPMVC\Tests\Fixtures\CircularOne;
use WPMVC\Tests\Fixtures\MessageReader;
use WPMVC\Tests\Fixtures\MessageSource;
use WPMVC\Tests\Fixtures\StaticMessageSource;

final class ContainerTest extends TestCase {

	public function testInterfaceBindingAndNestedResolution(): void {
		$container = new Container();
		$container->bind( MessageSource::class, StaticMessageSource::class );

		$reader = $container->make( MessageReader::class );

		self::assertInstanceOf( StaticMessageSource::class, $reader->source );
		self::assertSame( 'ready', $reader->source->message() );
	}

	public function testSingletonAliasAndExplicitOverrides(): void {
		$container = new Container();
		$container->singleton( MessageSource::class, StaticMessageSource::class );
		$container->alias( MessageSource::class, 'messages' );

		self::assertSame( $container->make( MessageSource::class ), $container->make( 'messages' ) );

		$override = new StaticMessageSource();
		$reader   = $container->make( MessageReader::class, [ MessageSource::class => $override ] );
		self::assertSame( $override, $reader->source );
	}

	public function testCallableInjectionAndOverrides(): void {
		$container = new Container();
		$container->bind( MessageSource::class, StaticMessageSource::class );

		$result = $container->call(
			static fn ( MessageSource $source, string $prefix ): string => $prefix . $source->message(),
			[ 'prefix' => 'status: ' ]
		);

		self::assertSame( 'status: ready', $result );
	}

	public function testCircularDependencyReportsPath(): void {
		$container = new Container();

		$this->expectException( CircularDependencyException::class );
		$this->expectExceptionMessage( CircularOne::class );
		$container->make( CircularOne::class );
	}

	public function testMissingInterfaceProducesUsefulError(): void {
		$container = new Container();

		$this->expectException( BindingResolutionException::class );
		$this->expectExceptionMessage( MessageSource::class );
		$container->make( MessageSource::class );
	}

	public function testBindingMustSatisfyItsAbstractType(): void {
		$container = new Container();
		$container->bind( MessageSource::class, \stdClass::class );

		$this->expectException( BindingResolutionException::class );
		$this->expectExceptionMessage( MessageSource::class );
		$container->make( MessageSource::class );
	}
}
