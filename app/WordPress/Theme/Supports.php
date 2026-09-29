<?php

declare(strict_types=1);

namespace WPMVC\WordPress\Theme;

final class Supports {

	public function register(): void {
		add_action( 'after_setup_theme', [ $this, 'setup' ] );
	}

	public function setup(): void {
		load_theme_textdomain( 'wpmvc-theme', get_template_directory() . '/languages' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support(
			'html5',
			[
				'comment-list',
				'comment-form',
				'search-form',
				'gallery',
				'caption',
				'style',
				'script',
			]
		);
		add_theme_support(
			'custom-logo',
			[
				'height'      => 120,
				'width'       => 420,
				'flex-height' => true,
				'flex-width'  => true,
			]
		);
	}
}
