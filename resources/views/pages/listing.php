<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$listing = $data->get( 'listing' );

if ( ! $listing instanceof \WPMVC\Data\PostCollection ) {
	return;
}
?>
<div class="container page-shell">
	<header class="page-header">
		<h1><?php echo esc_html( (string) $data->get( 'title', __( 'Posts', 'wpmvc-theme' ) ) ); ?></h1>
		<?php if ( $data->has( 'description' ) && $data->get( 'description' ) !== '' ) : ?>
			<div class="page-header__description"><?php echo wp_kses_post( (string) $data->get( 'description' ) ); ?></div>
		<?php endif; ?>
	</header>
	<?php if ( $data->get( 'showSearch', false ) ) : ?>
		<?php get_search_form(); ?>
	<?php endif; ?>
	<?php if ( $listing->isEmpty() ) : ?>
		<p class="empty-state"><?php esc_html_e( 'No posts were found.', 'wpmvc-theme' ); ?></p>
	<?php else : ?>
		<div class="post-grid">
			<?php foreach ( $listing->posts as $itemPost ) : ?>
				<?php echo $view->render( 'components.post-card', [ 'post' => $itemPost ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered component escapes its output. ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $listing->totalPages > 1 ) : ?>
			<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts pagination', 'wpmvc-theme' ); ?>">
				<?php
					$pagination = paginate_links(
						[
							'current'   => $listing->currentPage,
							'total'     => $listing->totalPages,
							'prev_text' => __( 'Previous', 'wpmvc-theme' ),
							'next_text' => __( 'Next', 'wpmvc-theme' ),
						]
					);
					echo wp_kses_post( $pagination === false ? '' : $pagination );
				?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</div>
