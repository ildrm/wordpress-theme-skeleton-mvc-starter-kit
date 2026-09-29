<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

if ( is_active_sidebar( 'primary' ) ) :
	?>
	<aside class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'wpmvc-theme' ); ?>">
		<?php dynamic_sidebar( 'primary' ); ?>
	</aside>
<?php endif; ?>
