<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$currentPost = $data->get( 'post' );

if ( ! $currentPost instanceof WP_Post ) {
	return;
}

// The main query's loop establishes WordPress post globals for content filters,
// page links, comments, and third-party integrations.
rewind_posts();
the_post();
?>
<div class="container page-shell">
	<article <?php post_class( 'entry', $currentPost ); ?>>
		<header class="entry__header">
			<h1><?php echo esc_html( get_the_title( $currentPost ) ); ?></h1>
			<?php if ( $currentPost->post_type === 'post' ) : ?>
				<p class="entry__meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $currentPost ) ); ?>"><?php echo esc_html( get_the_date( '', $currentPost ) ); ?></time></p>
			<?php endif; ?>
		</header>
		<?php if ( has_post_thumbnail( $currentPost ) ) : ?>
			<div class="entry__image"><?php echo wp_kses_post( get_the_post_thumbnail( $currentPost, 'large' ) ); ?></div>
		<?php endif; ?>
		<div class="entry__content">
			<?php echo wp_kses_post( apply_filters( 'the_content', get_the_content( null, false, $currentPost ) ) ); ?>
			<?php
			wp_link_pages(
				[
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page navigation', 'wpmvc-theme' ) . '">',
					'after'  => '</nav>',
				]
			);
			?>
		</div>
	</article>
	<?php if ( comments_open( $currentPost ) || get_comments_number( $currentPost ) > 0 ) : ?>
		<?php comments_template(); ?>
	<?php endif; ?>
</div>
<?php rewind_posts(); ?>
