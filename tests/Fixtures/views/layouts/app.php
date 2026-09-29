<?php

declare(strict_types=1);

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Test fixture escapes site text; content is already rendered HTML.
echo '<main>' . htmlspecialchars( (string) $data->get( 'site' ), ENT_QUOTES, 'UTF-8' ) . ': ' . $data->get( 'content' ) . '</main>';
