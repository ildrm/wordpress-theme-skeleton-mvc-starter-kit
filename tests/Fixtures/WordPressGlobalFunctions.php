<?php

declare(strict_types=1);

namespace WPMVC\Http\Requests;

function wp_unslash( mixed $value ): mixed {
	return $value;
}
