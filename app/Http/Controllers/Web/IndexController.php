<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Web;

use WPMVC\Contracts\Repositories\PostRepository;
use WPMVC\View\View;
use WPMVC\View\ViewFactory;

final class IndexController {

	public function __construct(
		private readonly PostRepository $posts,
		private readonly ViewFactory $views,
	) {
	}

	public function index(): View {
		return $this->views->make(
			'pages.listing',
			[
				'title'   => __( 'Latest posts', 'wpmvc-theme' ),
				'listing' => $this->posts->listing(),
			]
		)->layout( 'layouts.app' );
	}
}
