<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Rest;

/** A read-only endpoint for publicly visible theme identity. */
final class ThemeInfoController {
	/** @return array{name: string, version: string} */
	public function show(): array {
		$theme = wp_get_theme();
		return [
			'name'    => (string) $theme->get( 'Name' ),
			'version' => (string) $theme->get( 'Version' ),
		];
	}
}
