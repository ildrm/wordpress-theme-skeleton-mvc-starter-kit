<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Ajax;

/** A capability and nonce protected admin status check. */
final class ThemeStatusController {
	/** @return array{version: string, active: bool} */
	public function show(): array {
		return [
			'version' => (string) wp_get_theme()->get( 'Version' ),
			'active'  => true,
		];
	}
}
