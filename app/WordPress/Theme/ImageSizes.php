<?php

declare(strict_types=1);

namespace WPMVC\WordPress\Theme;

use function WPMVC\Support\config;

final class ImageSizes {

	public function register(): void {
		add_action( 'after_setup_theme', [ $this, 'setup' ] );
	}

	public function setup(): void {
		$sizes = config( 'theme.image_sizes', [] );

		if ( ! is_array( $sizes ) ) {
			return;
		}

		foreach ( $sizes as $name => $size ) {
			if ( ! is_string( $name ) || ! is_array( $size ) || sanitize_key( $name ) !== $name ) {
				continue;
			}

			$width  = (int) ( $size['width'] ?? 0 );
			$height = (int) ( $size['height'] ?? 0 );
			$crop   = $size['crop'] ?? false;

			if ( $width < 1 && $height < 1 ) {
				continue;
			}

			add_image_size( $name, max( 0, $width ), max( 0, $height ), is_bool( $crop ) || is_array( $crop ) ? $crop : false );
		}
	}
}
