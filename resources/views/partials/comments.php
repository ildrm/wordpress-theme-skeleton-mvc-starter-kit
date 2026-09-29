<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area" aria-labelledby="comments-title">
	<?php if ( have_comments() ) : ?>
		<h2 id="comments-title">
			<?php
			$commentCount = (int) get_comments_number();
			/* translators: %s is a number of comments. */
			echo esc_html( sprintf( _n( '%s comment', '%s comments', $commentCount, 'wpmvc-theme' ), number_format_i18n( $commentCount ) ) );
			?>
		</h2>
		<ol class="comment-list">
		<?php
		wp_list_comments(
			[
				'style'       => 'ol',
				'avatar_size' => 48,
			]
		);
		?>
									</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>
	<?php if ( comments_open() ) : ?>
		<?php comment_form(); ?>
	<?php endif; ?>
</section>
