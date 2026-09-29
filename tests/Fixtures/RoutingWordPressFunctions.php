<?php

declare(strict_types=1);

namespace WPMVC\Routing;

function add_action( string $hook, callable $callback ): void {
	RoutingWordPressState::$hooks[ $hook ] = $callback;
}

/** @param array<string, mixed> $args */
function register_rest_route( string $namespace, string $path, array $args ): void {
	RoutingWordPressState::$rest[ $namespace . $path ] = $args;
}

function current_user_can( string $capability ): bool {
	return RoutingWordPressState::$capabilityAllowed && '' !== $capability;
}

function wp_verify_nonce( string $nonce, string $action ): bool {
	return RoutingWordPressState::$nonceValid && '' !== $nonce && '' !== $action;
}

function sanitize_text_field( string $value ): string {
	return trim( $value );
}

function wp_unslash( mixed $value ): mixed {
	return $value;
}

function wp_send_json_error( mixed $data, int $status = 200 ): void {
	RoutingWordPressState::$json = [
		'kind'   => 'error',
		'data'   => $data,
		'status' => $status,
	];
	throw new JsonSent();
}

function wp_send_json_success( mixed $data, int $status = 200 ): void {
	RoutingWordPressState::$json = [
		'kind'   => 'success',
		'data'   => $data,
		'status' => $status,
	];
	throw new JsonSent();
}
