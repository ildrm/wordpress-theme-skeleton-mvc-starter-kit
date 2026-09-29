<?php

declare(strict_types=1);

namespace WPMVC\Repositories\WordPress;

use WP_Post;
use WP_Query;
use WPMVC\Contracts\Repositories\PostRepository;
use WPMVC\Data\PostCollection;

/** Reads the main query selected by WordPress; never starts a second query. */
final class WordPressPostRepository implements PostRepository {

	public function current(): ?WP_Post {
		$object = get_queried_object();

		return $object instanceof WP_Post ? $object : null;
	}

	public function listing(): PostCollection {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query ) {
			return new PostCollection( [], 1, 1 );
		}

		$posts = array_values(
			array_filter(
				$wp_query->posts,
				static fn ( mixed $post ): bool => $post instanceof WP_Post
			)
		);

		return new PostCollection(
			$posts,
			max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
			max( 1, (int) $wp_query->max_num_pages ),
		);
	}
}
