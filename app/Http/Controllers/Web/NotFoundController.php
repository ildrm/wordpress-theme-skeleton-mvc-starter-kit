<?php

declare(strict_types=1);

namespace WPMVC\Http\Controllers\Web;

use WPMVC\View\View;
use WPMVC\View\ViewFactory;

final class NotFoundController {

	public function __construct( private readonly ViewFactory $views ) {
	}

	public function show(): View {
		return $this->views->make( 'errors.404' )->layout( 'layouts.app' );
	}
}
