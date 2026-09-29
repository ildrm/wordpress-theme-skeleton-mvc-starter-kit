<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'wpmvc-theme' ); ?></a>
<?php echo $view->render( 'partials.header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered theme view escapes its own output. ?>
<main id="main-content" class="site-main">
	<?php echo (string) $data->get( 'content', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered child view escapes its own output. ?>
</main>
<?php echo $view->render( 'partials.footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered theme view escapes its own output. ?>
<?php wp_footer(); ?>
</body>
</html>
