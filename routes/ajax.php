<?php

declare(strict_types=1);

use WPMVC\Http\Controllers\Ajax\ThemeStatusController;
use WPMVC\Routing\AjaxRouter;

/** @var AjaxRouter $router */
$router->post(
	'wpmvc_theme_status',
	[ ThemeStatusController::class, 'show' ],
	[
		'nonce_action' => 'wpmvc_theme_status',
		'capability'   => 'manage_options',
	]
);
