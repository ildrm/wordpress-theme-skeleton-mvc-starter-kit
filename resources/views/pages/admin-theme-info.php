<?php

declare(strict_types=1);

/** @var \WPMVC\View\ViewData $data */
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Theme information', 'wpmvc-theme' ); ?></h1>
	<p><?php echo esc_html( (string) $data->get( 'name', '' ) ); ?></p>
	<p><?php echo esc_html( sprintf( /* translators: %s: theme version number. */ __( 'Version %s', 'wpmvc-theme' ), (string) $data->get( 'version', '' ) ) ); ?></p>
</div>
