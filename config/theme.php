<?php

declare(strict_types=1);

return [
	'text_domain' => 'wpmvc-theme',
	'menus'       => [
		'primary' => static fn (): string => __( 'Primary Navigation', 'wpmvc-theme' ),
		'footer'  => static fn (): string => __( 'Footer Navigation', 'wpmvc-theme' ),
	],
	'sidebars'    => [
		'primary' => [
			'name'        => static fn (): string => __( 'Primary Sidebar', 'wpmvc-theme' ),
			'description' => static fn (): string => __( 'Optional widgets for templates that include the sidebar.', 'wpmvc-theme' ),
		],
	],
	'image_sizes' => [],
];
