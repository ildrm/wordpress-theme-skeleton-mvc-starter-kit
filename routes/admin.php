<?php

declare(strict_types=1);

use WPMVC\Http\Controllers\Admin\ThemeInfoController;
use WPMVC\Routing\AdminRouter;

/** @var AdminRouter $router */
$router->submenu(
	'themes.php',
	'wpmvc-theme-info',
	__( 'Theme information', 'wpmvc-theme' ),
	__( 'Theme information', 'wpmvc-theme' ),
	'manage_options',
	[ ThemeInfoController::class, 'show' ]
);
