<?php

declare(strict_types=1);

namespace WPMVC\WordPress\Menus;

use function WPMVC\Support\config;

final class MenuRegistrar {

	public function register(): void {
		add_action( 'after_setup_theme', [ $this, 'setup' ] );
	}

	public function setup(): void {
		$configured = config( 'theme.menus', [] );
		if ( ! is_array( $configured ) ) {
			return;
		}

		$locations = [];
		foreach ( $configured as $slug => $label ) {
			if ( ! is_string( $slug ) || sanitize_key( $slug ) !== $slug ) {
				continue;
			}

			$name = is_callable( $label ) ? $label() : $label;
			if ( is_string( $name ) && '' !== $name ) {
				$locations[ $slug ] = $name;
			}
		}

		if ( [] !== $locations ) {
			register_nav_menus( $locations );
		}
	}
}
