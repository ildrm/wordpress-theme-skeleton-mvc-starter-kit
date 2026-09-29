<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

interface MessageSource {
	public function message(): string;
}
