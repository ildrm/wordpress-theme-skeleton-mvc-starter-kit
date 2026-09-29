<?php

declare(strict_types=1);

return [
	'name'        => 'WPMVC Theme',
	'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	'debug'       => defined( 'WP_DEBUG' ) && WP_DEBUG,
];
