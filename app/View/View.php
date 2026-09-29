<?php

declare(strict_types=1);

namespace WPMVC\View;

final class View {

	/** @var ?string Optional layout view rendered around this view's content. */
	private ?string $layout = null;

	public function __construct(
		private readonly ViewFactory $factory,
		private readonly string $name,
		private readonly ViewData $data
	) {
	}

	public function layout( string $name ): self {
		$view         = clone $this;
		$view->layout = $name;

		return $view;
	}

	public function name(): string {
		return $this->name;
	}

	public function data(): ViewData {
		return $this->data;
	}

	public function render(): string {
		$data    = $this->factory->compose( $this->name, $this->data );
		$content = $this->factory->renderTemplate( $this->name, $data );

		if ( $this->layout === null ) {
			return $content;
		}

		return $this->factory->render( $this->layout, $data->with( 'content', $content ) );
	}

	public function __toString(): string {
		return $this->render();
	}
}
