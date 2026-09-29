<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

final class MessageReader {
	public function __construct( public readonly MessageSource $source ) {
	}
}
