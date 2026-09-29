<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<p>
			<?php
			/* translators: %s is the site name. */
			echo esc_html( sprintf( __( '© %s. All rights reserved.', 'wpmvc-theme' ), get_bloginfo( 'name' ) ) );
			?>
		</p>
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Footer navigation', 'wpmvc-theme' ); ?>">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-navigation__list',
						'depth'          => 1,
					]
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</footer>
