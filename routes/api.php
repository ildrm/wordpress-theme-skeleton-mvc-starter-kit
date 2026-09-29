<?php

declare(strict_types=1);

use WPMVC\Http\Controllers\Rest\ThemeInfoController;
use WPMVC\Routing\RestRouter;

/** @var RestRouter $router */
$router->get( '/theme', [ ThemeInfoController::class, 'show' ], [ 'permission' => '__return_true' ] );
$router->get( '/admin/theme', [ ThemeInfoController::class, 'show' ], [ 'permission' => 'manage_options' ] );
