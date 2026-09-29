<?php

declare(strict_types=1);

namespace WPMVC\Tests\Unit;

require_once dirname( __DIR__ ) . '/Fixtures/WordPressRestRequest.php';
require_once dirname( __DIR__ ) . '/Fixtures/RoutingWordPressState.php';
require_once dirname( __DIR__ ) . '/Fixtures/JsonSent.php';
require_once dirname( __DIR__ ) . '/Fixtures/RoutingWordPressFunctions.php';
require_once dirname( __DIR__ ) . '/Fixtures/WordPressGlobalFunctions.php';

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use WPMVC\Foundation\Application;
use WPMVC\Routing\ActionDispatcher;
use WPMVC\Routing\AjaxRouter;
use WPMVC\Routing\JsonSent;
use WPMVC\Routing\RestRouter;
use WPMVC\Routing\RouteCollection;
use WPMVC\Routing\RoutingWordPressState;
use WPMVC\Routing\TemplateDispatcher;
use WPMVC\Routing\TemplateRouter;
use WPMVC\View\ViewFactory;
use WPMVC\View\ViewFinder;

final class RoutingTest extends TestCase {
	protected function setUp(): void {
		RoutingWordPressState::reset();
	}

	public function testTemplateRoutesRejectDuplicatesAndMissingKeys(): void {
		$router = new TemplateRouter( new RouteCollection() );
		$router->template( 'single-product', static fn () => null );
		self::assertSame( 'single-product', $router->route( 'single-product' )->key() );

		try {
			$router->template( 'single-product', static fn () => null );
			self::fail( 'Duplicate route was accepted.' );
		} catch ( InvalidArgumentException ) {
			self::assertTrue( true );
		}

		$this->expectException( OutOfBoundsException::class );
		$router->route( 'unknown' );
	}

	public function testTemplateDispatcherRendersTheControllerView(): void {
		$app    = new Application( dirname( __DIR__, 2 ) );
		$router = new TemplateRouter( new RouteCollection() );
		$views  = new ViewFactory( new ViewFinder( [ dirname( __DIR__ ) . '/Fixtures/views' ] ) );
		$router->template( 'page', static fn () => $views->make( 'pages.greeting', [ 'person' => 'Ada' ] ) );

		ob_start();
		( new TemplateDispatcher( $app, $router ) )->dispatch( 'page' );
		self::assertSame( 'Hello Ada', trim( (string) ob_get_clean() ) );
	}

	public function testRestRouteRequiresPermissionAndRegistersOnRestHook(): void {
		$router = new RestRouter( new ActionDispatcher( new Application( dirname( __DIR__, 2 ) ) ) );

		$this->expectException( InvalidArgumentException::class );
		$router->get( '/unsafe', static fn () => [], [] );
	}

	public function testRestRegistrationUsesExplicitPermissionCallback(): void {
		$router = new RestRouter( new ActionDispatcher( new Application( dirname( __DIR__, 2 ) ) ) );
		$router->get( '/private', static fn () => [], [ 'permission' => 'manage_options' ] );
		$router->hook();
		self::assertArrayHasKey( 'rest_api_init', RoutingWordPressState::$hooks );
		self::assertSame( [], RoutingWordPressState::$rest );
		( RoutingWordPressState::$hooks['rest_api_init'] )();

		$route = RoutingWordPressState::$rest['wpmvc/v1/private'];
		self::assertSame( 'GET', $route['methods'] );
		self::assertIsCallable( $route['permission_callback'] );
		self::assertFalse( $route['permission_callback']( new \WP_REST_Request() ) );
		RoutingWordPressState::$capabilityAllowed = true;
		self::assertTrue( $route['permission_callback']( new \WP_REST_Request() ) );
	}

	public function testRestCustomSanitizerRetainsSchemaValidation(): void {
		$router = new RestRouter( new ActionDispatcher( new Application( dirname( __DIR__, 2 ) ) ) );
		$router->get(
			'/typed',
			static fn () => [],
			[
				'permission' => '__return_true',
				'args'       => [
					'id' => [
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
		$router->register();
		self::assertSame( 'rest_validate_request_arg', RoutingWordPressState::$rest['wpmvc/v1/typed']['args']['id']['validate_callback'] );
	}

	public function testAjaxRejectsMissingNonceAndCapability(): void {
		$router = new AjaxRouter( new ActionDispatcher( new Application( dirname( __DIR__, 2 ) ) ) );
		$router->post(
			'wpmvc_test',
			static fn () => [ 'ok' => true ],
			[
				'nonce_action' => 'wpmvc_test',
				'capability'   => 'manage_options',
			]
		);
		$router->register();

		self::assertArrayHasKey( 'wp_ajax_wpmvc_test', RoutingWordPressState::$hooks );
		self::assertArrayNotHasKey( 'wp_ajax_nopriv_wpmvc_test', RoutingWordPressState::$hooks );
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['_ajax_nonce']      = 'nonce';

		$this->dispatchAjax();
		self::assertSame( 403, RoutingWordPressState::$json['status'] );
		self::assertSame( 'invalid_nonce', RoutingWordPressState::$json['data']['code'] );

		RoutingWordPressState::$nonceValid = true;
		$this->dispatchAjax();
		self::assertSame( 403, RoutingWordPressState::$json['status'] );
		self::assertSame( 'forbidden', RoutingWordPressState::$json['data']['code'] );

		RoutingWordPressState::$capabilityAllowed = true;
		$this->dispatchAjax();
		self::assertSame( 'success', RoutingWordPressState::$json['kind'] );
		self::assertSame( [ 'ok' => true ], RoutingWordPressState::$json['data'] );
		unset( $_SERVER['REQUEST_METHOD'], $_POST['_ajax_nonce'] );
	}

	private function dispatchAjax(): void {
		try {
			( RoutingWordPressState::$hooks['wp_ajax_wpmvc_test'] )();
			self::fail( 'AJAX response did not end the request.' );
		} catch ( JsonSent $exception ) {
			self::assertNotNull( RoutingWordPressState::$json );
		}
	}
}
