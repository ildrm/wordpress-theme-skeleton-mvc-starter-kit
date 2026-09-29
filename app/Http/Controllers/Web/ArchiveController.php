<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Web;

use WPMVC\Contracts\Repositories\PostRepository;
use WPMVC\View\View;
use WPMVC\View\ViewFactory;

final class ArchiveController {

	public function __construct(
		private readonly PostRepository $posts,
		private readonly ViewFactory $views,
	) {
	}

	public function index(): View {
		return $this->views->make(
			'pages.listing',
			[
				'title'       => wp_strip_all_tags( get_the_archive_title() ),
				'description' => get_the_archive_description(),
				'listing'     => $this->posts->listing(),
			]
		)->layout( 'layouts.app' );
	}
}
