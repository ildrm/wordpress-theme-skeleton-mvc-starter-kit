<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

final class CircularOne {
	public function __construct( CircularTwo $two ) {
	}
}
