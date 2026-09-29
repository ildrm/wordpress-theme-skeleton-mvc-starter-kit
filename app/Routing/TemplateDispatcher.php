<?php

declare(strict_types=1);

namespace WPMVC\Routing;

use RuntimeException;
use WPMVC\Foundation\Application;
use WPMVC\View\View;

/** Dispatches a template selected by WordPress; it never selects a URL route. */
final class TemplateDispatcher {
	public function __construct(
		private readonly Application $app,
		private readonly TemplateRouter $router,
	) {}

	public function dispatch( string $key ): void {
		$action = $this->router->route( $key )->action();
		$result = ( new ActionDispatcher( $this->app ) )->call( $action );
		if ( $result instanceof View ) {
			echo $result->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The view escapes values in their output contexts.
			return;
		}

		throw new RuntimeException( sprintf( 'Template route "%s" must return a View.', $key ) );
	}
}
