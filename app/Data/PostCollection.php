<?php

declare(strict_types=1);

namespace WPMVC\Data;

use WP_Post;

final readonly class PostCollection {

	/**
	 * @param list<WP_Post> $posts
	 */
	public function __construct(
		public array $posts,
		public int $currentPage,
		public int $totalPages,
	) {
	}

	public function isEmpty(): bool {
		return $this->posts === [];
	}
}
