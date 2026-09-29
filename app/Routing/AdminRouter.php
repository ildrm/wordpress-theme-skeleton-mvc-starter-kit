<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use InvalidArgumentException;
use RuntimeException;
use WPMVC\View\View;

/** WordPress admin menu declarations, separate from page controllers. */
final class AdminRouter {
	/** @var list<array{parent: ?string, slug: string, page_title: string, menu_title: string, capability: string, action: callable|array<int, mixed>, icon: string, position: ?int}> */
	private array $pages = [];

	public function __construct( private readonly ActionDispatcher $dispatcher ) {}

	/** @param callable|array{0: class-string|object, 1: string} $action */
	public function menu( string $slug, string $pageTitle, string $menuTitle, string $capability, callable|array $action, string $icon = '', ?int $position = null ): self {
		return $this->add( null, $slug, $pageTitle, $menuTitle, $capability, $action, $icon, $position );
	}

	/** @param callable|array{0: class-string|object, 1: string} $action */
	public function submenu( string $parent, string $slug, string $pageTitle, string $menuTitle, string $capability, callable|array $action ): self {
		if ( '' === $parent ) {
			throw new InvalidArgumentException( 'Admin submenu requires a parent slug.' );
		}

		return $this->add( $parent, $slug, $pageTitle, $menuTitle, $capability, $action, '', null );
	}

	/** @param callable|array{0: class-string|object, 1: string} $action */
	private function add( ?string $parent, string $slug, string $pageTitle, string $menuTitle, string $capability, callable|array $action, string $icon, ?int $position ): self {
		if ( ! preg_match( '/^[a-z0-9_-]+$/', $slug ) || '' === $pageTitle || '' === $menuTitle || '' === $capability ) {
			throw new InvalidArgumentException( 'Admin pages require a valid slug, titles, and capability.' );
		}

		foreach ( $this->pages as $page ) {
			if ( $page['slug'] === $slug ) {
				throw new InvalidArgumentException( sprintf( 'Admin page "%s" is already registered.', $slug ) );
			}
		}

		$this->pages[] = [
			'parent'     => $parent,
			'slug'       => $slug,
			'page_title' => $pageTitle,
			'menu_title' => $menuTitle,
			'capability' => $capability,
			'action'     => $action,
			'icon'       => $icon,
			'position'   => $position,
		];
		return $this;
	}

	public function hook(): void {
		add_action( 'admin_menu', $this->register( ... ) );
	}

	public function register(): void {
		foreach ( $this->pages as $page ) {
			$render = fn () => $this->render( $page );
			if ( null === $page['parent'] ) {
				add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['slug'], $render, $page['icon'], $page['position'] );
			} else {
				add_submenu_page( $page['parent'], $page['page_title'], $page['menu_title'], $page['capability'], $page['slug'], $render );
			}
		}
	}

	/** @param array{parent: ?string, slug: string, page_title: string, menu_title: string, capability: string, action: callable|array<int, mixed>, icon: string, position: ?int} $page */
	private function render( array $page ): void {
		if ( ! current_user_can( $page['capability'] ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'wpmvc-theme' ), '', [ 'response' => 403 ] );
		}

		$view = $this->dispatcher->call( $page['action'] );
		if ( ! $view instanceof View ) {
			throw new RuntimeException( sprintf( 'Admin page "%s" must return a View.', $page['slug'] ) );
		}

		echo $view->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Values are escaped by the admin view.
	}
}
