<?php

declare(strict_types=1);

namespace WPMVC\Tests\Fixtures;

final class StaticMessageSource implements MessageSource {
	public function message(): string {
		return 'ready';
	}
}
