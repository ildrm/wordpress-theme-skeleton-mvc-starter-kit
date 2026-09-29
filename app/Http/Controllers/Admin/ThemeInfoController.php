<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Admin;

use WPMVC\View\View;
use WPMVC\View\ViewFactory;

final class ThemeInfoController {
	public function __construct( private readonly ViewFactory $views ) {}

	public function show(): View {
		$theme = wp_get_theme();
		return $this->views->make(
			'pages.admin-theme-info',
			[
				'name'    => (string) $theme->get( 'Name' ),
				'version' => (string) $theme->get( 'Version' ),
			]
		);
	}
}
