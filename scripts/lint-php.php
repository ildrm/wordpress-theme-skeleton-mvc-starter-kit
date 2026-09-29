<?php
declare(strict_types=1);

$root        = dirname( __DIR__ );
$directories = [ 'app', 'bootstrap', 'config', 'routes', 'resources/views', 'tests' ];
$files       = glob( $root . '/*.php' );
if ( false === $files ) {
	$files = [];
}

foreach ( $directories as $directory ) {
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $root . '/' . $directory, FilesystemIterator::SKIP_DOTS )
	);

	foreach ( $iterator as $file ) {
		if ( $file->getExtension() === 'php' ) {
			$files[] = $file->getPathname();
		}
	}
}

sort( $files );
foreach ( $files as $file ) {
	$command = escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $file ) . ' 2>&1';
	exec( $command, $output, $status ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec -- CLI-only syntax check.
	if ( $status !== 0 ) {
		fwrite( STDERR, implode( "\n", $output ) . "\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI-only stderr output.
		exit( $status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI process exit status.
	}
	$output = [];
}

fwrite( STDOUT, sprintf( "PHP syntax valid in %d files.\n", count( $files ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI-only stdout output.
