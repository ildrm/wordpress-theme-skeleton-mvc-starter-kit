<?php

declare(strict_types=1);

use WPMVC\Foundation\Application;

if ( ! defined( 'ABSPATH' ) ) {
	if ( PHP_SAPI !== 'cli' ) {
		http_response_code( 403 );
		exit;
	}

	throw new RuntimeException( 'The theme application must be bootstrapped inside WordPress.' );
}

if ( Application::current() !== null ) {
	return Application::current();
}

$application = new Application( dirname( __DIR__ ) );
Application::setCurrent( $application );

try {
	$application->loadConfiguration();

	/** @var list<class-string<\WPMVC\Foundation\ServiceProvider>> $providers */
	$providers = require __DIR__ . '/providers.php';

	foreach ( $providers as $provider ) {
		$application->register( $provider );
	}

	$application->boot();

	return $application;
} catch ( Throwable $exception ) {
	Application::setCurrent( null );
	throw $exception;
}
