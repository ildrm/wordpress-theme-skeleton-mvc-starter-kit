<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;
?>
<header class="site-header">
	<div class="container site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_kses_post( get_custom_logo() ); ?>
			<?php else : ?>
				<a class="site-branding__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
			<?php endif; ?>
			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p class="site-branding__description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			<?php endif; ?>
		</div>
		<button class="navigation-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" data-navigation-toggle>
			<span class="navigation-toggle__label"><?php esc_html_e( 'Menu', 'wpmvc-theme' ); ?></span>
		</button>
		<nav id="primary-navigation" class="site-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'wpmvc-theme' ); ?>" data-navigation>
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-navigation__list',
						'depth'          => 2,
					]
				);
				?>
			<?php else : ?>
				<ul class="site-navigation__list">
					<?php wp_list_pages( [ 'title_li' => '' ] ); ?>
				</ul>
			<?php endif; ?>
		</nav>
	</div>
</header>
