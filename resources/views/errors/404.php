<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;
?>
<div class="container page-shell error-page">
	<h1><?php esc_html_e( 'Page not found', 'wpmvc-theme' ); ?></h1>
	<p><?php esc_html_e( 'The page you requested could not be found. Try a search or return to the home page.', 'wpmvc-theme' ); ?></p>
	<?php get_search_form(); ?>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to home', 'wpmvc-theme' ); ?></a></p>
</div>
