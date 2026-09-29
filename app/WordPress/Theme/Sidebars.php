<?php

declare(strict_types=1);

namespace WPMVC\WordPress\Theme;

use function WPMVC\Support\config;

final class Sidebars {

	public function register(): void {
		add_action( 'widgets_init', [ $this, 'setup' ] );
	}

	public function setup(): void {
		$configured = config( 'theme.sidebars', [] );
		if ( ! is_array( $configured ) ) {
			return;
		}

		foreach ( $configured as $id => $settings ) {
			if ( ! is_string( $id ) || sanitize_key( $id ) !== $id || ! is_array( $settings ) ) {
				continue;
			}

			$name        = $settings['name'] ?? '';
			$description = $settings['description'] ?? '';
			$name        = is_callable( $name ) ? $name() : $name;
			$description = is_callable( $description ) ? $description() : $description;
			if ( ! is_string( $name ) || '' === $name || ! is_string( $description ) ) {
				continue;
			}

			register_sidebar(
				[
					'id'            => $id,
					'name'          => $name,
					'description'   => $description,
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget__title">',
					'after_title'   => '</h2>',
				]
			);
		}
	}
}
