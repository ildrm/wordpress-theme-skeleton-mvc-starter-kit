<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

final class CircularTwo {
	public function __construct( CircularOne $one ) {
	}
}
