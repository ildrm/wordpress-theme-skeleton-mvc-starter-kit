<?php

declare(strict_types=1);

return [
	'manifest'        => 'public/build/.vite/manifest.json',
	'build_directory' => 'public/build',
	'entries'         => [
		'frontend' => 'resources/js/app.js',
		'admin'    => 'resources/css/admin.css',
		'editor'   => 'resources/css/editor.css',
	],
];
