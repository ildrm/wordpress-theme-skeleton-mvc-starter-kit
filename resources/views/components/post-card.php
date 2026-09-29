<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$entryPost = $data->get( 'post' );

if ( ! $entryPost instanceof WP_Post ) {
	return;
}
?>
<article <?php post_class( 'post-card', $entryPost ); ?>>
	<?php if ( has_post_thumbnail( $entryPost ) ) : ?>
		<a class="post-card__image" href="<?php echo esc_url( get_permalink( $entryPost ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo wp_kses_post( get_the_post_thumbnail( $entryPost, 'medium_large' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="post-card__body">
		<p class="post-card__meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $entryPost ) ); ?>"><?php echo esc_html( get_the_date( '', $entryPost ) ); ?></time></p>
		<h2 class="post-card__title"><a href="<?php echo esc_url( get_permalink( $entryPost ) ); ?>"><?php echo esc_html( get_the_title( $entryPost ) ); ?></a></h2>
		<p class="post-card__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $entryPost ) ) ); ?></p>
		<a class="post-card__more" href="<?php echo esc_url( get_permalink( $entryPost ) ); ?>">
			<?php
			/* translators: %s is the post title. */
			echo esc_html( sprintf( __( 'Read %s', 'wpmvc-theme' ), get_the_title( $entryPost ) ) );
			?>
		</a>
	</div>
</article>
