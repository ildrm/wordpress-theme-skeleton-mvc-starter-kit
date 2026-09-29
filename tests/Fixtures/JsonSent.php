<?php

declare(strict_types=1);

namespace WPMVC\Routing;

/** Models the termination behavior of wp_send_json_* in unit tests. */
final class JsonSent extends \RuntimeException {}
