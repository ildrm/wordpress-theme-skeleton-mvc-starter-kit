<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$searchId = wp_unique_id( 'site-search-' );
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $searchId ); ?>"><?php esc_html_e( 'Search this site', 'wpmvc-theme' ); ?></label>
	<div class="search-form__controls">
		<input id="<?php echo esc_attr( $searchId ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" required>
		<button type="submit"><?php esc_html_e( 'Search', 'wpmvc-theme' ); ?></button>
	</div>
</form>
