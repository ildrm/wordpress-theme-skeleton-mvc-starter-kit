<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

echo \WPMVC\Foundation\Application::current()?->make( \WPMVC\View\ViewFactory::class )->render( 'partials.sidebar' ) ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered theme view escapes its output.
