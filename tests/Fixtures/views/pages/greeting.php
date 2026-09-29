<?php

declare(strict_types=1);

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Standalone test fixture uses native PHP escaping without WordPress.
echo 'Hello ' . htmlspecialchars( (string) $data->get( 'person' ), ENT_QUOTES, 'UTF-8' );
