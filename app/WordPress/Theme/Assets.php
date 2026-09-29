<?php

declare(strict_types=1);

namespace WPMVC\WordPress\Theme;

use WPMVC\Support\Vite;
use function WPMVC\Support\config;

/** Enqueues built assets only in the WordPress context that needs them. */
final class Assets {

	public function __construct( private readonly Vite $vite ) {
	}

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor' ] );
	}

	public function frontend(): void {
		if ( ! $this->enqueueConfigured( 'frontend', 'wpmvc-theme-app' ) ) {
			wp_enqueue_style( 'wpmvc-theme-fallback', get_template_directory_uri() . '/style.css', [], '1.0.0' );
		}

		if ( get_stylesheet_directory() !== get_template_directory() ) {
			wp_enqueue_style( 'wpmvc-theme-child', get_stylesheet_uri(), [], wp_get_theme()->get( 'Version' ) );
		}
	}

	public function admin( string $hookSuffix ): void {
		if ( $hookSuffix === 'appearance_page_wpmvc-theme-info' ) {
			$this->enqueueConfigured( 'admin', 'wpmvc-theme-admin' );
		}
	}

	public function editor(): void {
		$this->enqueueConfigured( 'editor', 'wpmvc-theme-editor' );
	}

	private function enqueueConfigured( string $name, string $handle ): bool {
		$entry = config( 'assets.entries.' . $name );

		return is_string( $entry ) ? $this->enqueue( $entry, $handle ) : false;
	}

	private function enqueue( string $entry, string $handle ): bool {
		$asset = $this->vite->asset( $entry );

		if ( $asset === null ) {
			return false;
		}

		foreach ( $asset['css'] as $index => $file ) {
			wp_enqueue_style( $handle . '-style-' . $index, $this->vite->url( $file ), [], '1.0.0' );
		}

		if ( str_ends_with( $asset['file'], '.css' ) ) {
			wp_enqueue_style( $handle, $this->vite->url( $asset['file'] ), [], '1.0.0' );
		} else {
			wp_enqueue_script_module( $handle, $this->vite->url( $asset['file'] ), [], '1.0.0' );
		}

		return true;
	}
}
