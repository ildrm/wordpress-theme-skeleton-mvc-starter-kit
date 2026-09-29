<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$app = \WPMVC\Foundation\Application::current();

if ( $app === null ) {
	throw new \RuntimeException( 'The theme application has not been bootstrapped.' );
}

$app->make( \WPMVC\Routing\TemplateDispatcher::class )->dispatch( 'index' );
