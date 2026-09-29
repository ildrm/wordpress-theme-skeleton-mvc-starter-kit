<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$views = \WPMVC\Foundation\Application::current()?->make( \WPMVC\View\ViewFactory::class );

if ( $views !== null ) {
	echo $views->render( 'partials.footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered theme view escapes its output.
	echo $views->render( 'partials.document-end' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered theme view escapes its output.
}
