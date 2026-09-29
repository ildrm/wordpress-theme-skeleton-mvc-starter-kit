<?php

declare(strict_types=1);

namespace WPMVC\Contracts\Repositories;

use WP_Post;
use WPMVC\Data\PostCollection;

interface PostRepository {

	public function current(): ?WP_Post;

	public function listing(): PostCollection;
}
