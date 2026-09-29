<?php

declare(strict_types=1);

namespace WPMVC\Support;

use RuntimeException;
use WPMVC\Foundation\Application;
use WPMVC\View\View;
use WPMVC\View\ViewData;
use WPMVC\View\ViewFactory;

/** Resolve the current theme application at WordPress entry boundaries. */
function app( ?string $abstract = null ): mixed {
	$application = Application::current();

	if ( $application === null ) {
		throw new RuntimeException( 'The theme application has not been bootstrapped.' );
	}

	return $abstract === null ? $application : $application->make( $abstract );
}

function config( ?string $key = null, mixed $default = null ): mixed {
	return app()->config()->get( $key, $default );
}

/** @param array<string, mixed>|ViewData $data */
function view( string $name, array|ViewData $data = [] ): View {
	return app( ViewFactory::class )->make( $name, $data );
}
