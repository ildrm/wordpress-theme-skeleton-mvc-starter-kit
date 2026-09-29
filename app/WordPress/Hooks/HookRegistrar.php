<?php

declare(strict_types=1);

namespace WPMVC\WordPress\Hooks;

/** Holds small, theme-specific WordPress filters outside the bootstrap. */
final class HookRegistrar {

	public function register(): void {
		add_filter( 'body_class', [ $this, 'bodyClasses' ] );
	}

	/**
	 * @param list<string> $classes
	 * @return list<string>
	 */
	public function bodyClasses( array $classes ): array {
		$classes[] = 'wpmvc-theme';

		return $classes;
	}
}
