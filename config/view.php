<?php

declare(strict_types=1);

$parent = dirname( __DIR__ );
$paths  = [ $parent . '/resources/views' ];

if ( function_exists( 'get_stylesheet_directory' ) ) {
	$child = get_stylesheet_directory();

	if ( $child !== $parent ) {
		array_unshift( $paths, $child . '/resources/views' );
	}
}

return [
	'paths'  => $paths,
	'layout' => 'layouts.app',
];
