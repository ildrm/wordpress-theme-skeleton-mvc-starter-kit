<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Web;

use WPMVC\Contracts\Repositories\PostRepository;
use WPMVC\View\View;
use WPMVC\View\ViewFactory;

final class SearchController {

	public function __construct(
		private readonly PostRepository $posts,
		private readonly ViewFactory $views,
	) {
	}

	public function index(): View {
		return $this->views->make(
			'pages.listing',
			[
				/* translators: %s is the visitor's search phrase. */
				'title'      => sprintf( __( 'Search results for “%s”', 'wpmvc-theme' ), get_search_query( false ) ),
				'listing'    => $this->posts->listing(),
				'showSearch' => true,
			]
		)->layout( 'layouts.app' );
	}
}
