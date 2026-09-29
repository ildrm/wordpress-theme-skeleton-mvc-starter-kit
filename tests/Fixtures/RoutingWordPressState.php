<?php

declare(strict_types=1);

namespace WPMVC\Routing;

final class RoutingWordPressState {
	/** @var array<string, callable> */
	public static array $hooks = [];
	/** @var array<string, array<string, mixed>> */
	public static array $rest = [];
	/** @var bool Whether the test nonce is accepted. */
	public static bool $nonceValid = false;
	/** @var bool Whether the test user has the requested capability. */
	public static bool $capabilityAllowed = false;
	/** @var array{kind: string, data: mixed, status: int}|null */
	public static ?array $json = null;

	public static function reset(): void {
		self::$hooks             = [];
		self::$rest              = [];
		self::$nonceValid        = false;
		self::$capabilityAllowed = false;
		self::$json              = null;
	}
}
