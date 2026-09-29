<?php

declare(strict_types=1);

use WPMVC\Http\Controllers\Web\ArchiveController;
use WPMVC\Http\Controllers\Web\FrontPageController;
use WPMVC\Http\Controllers\Web\HomeController;
use WPMVC\Http\Controllers\Web\IndexController;
use WPMVC\Http\Controllers\Web\NotFoundController;
use WPMVC\Http\Controllers\Web\PageController;
use WPMVC\Http\Controllers\Web\SearchController;
use WPMVC\Http\Controllers\Web\SingleController;
use WPMVC\Routing\TemplateRouter;

/** @var TemplateRouter $router */
$router->template( 'index', [ IndexController::class, 'index' ] );
$router->template( 'front-page', [ FrontPageController::class, 'show' ] );
$router->template( 'home', [ HomeController::class, 'index' ] );
$router->template( 'single', [ SingleController::class, 'show' ] );
$router->template( 'singular', [ SingleController::class, 'show' ] );
$router->template( 'attachment', [ SingleController::class, 'show' ] );
$router->template( 'page', [ PageController::class, 'show' ] );

foreach ( [ 'archive', 'author', 'category', 'tag', 'taxonomy', 'date' ] as $key ) {
	$router->template( $key, [ ArchiveController::class, 'index' ] );
}

$router->template( 'search', [ SearchController::class, 'index' ] );
$router->template( '404', [ NotFoundController::class, 'show' ] );
