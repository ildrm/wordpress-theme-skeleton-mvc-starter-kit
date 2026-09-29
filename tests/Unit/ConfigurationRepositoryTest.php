<?php

declare(strict_types=1);

namespace WPMVC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPMVC\Foundation\Configuration\Repository;

final class ConfigurationRepositoryTest extends TestCase {

	public function testDotNotationPreservesNullAndDefaults(): void {
		$config = new Repository(
			[
				'theme' => [
					'name'     => 'Base',
					'nullable' => null,
				],
			]
		);

		self::assertSame( 'Base', $config->get( 'theme.name' ) );
		self::assertNull( $config->get( 'theme.nullable', 'fallback' ) );
		self::assertTrue( $config->has( 'theme.nullable' ) );
		self::assertSame( 'fallback', $config->get( 'theme.absent', 'fallback' ) );

		$config->set( 'theme.colors.primary', '#000' );
		self::assertSame( '#000', $config->get( 'theme.colors.primary' ) );
	}
}
