<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress;

if ( defined( 'MULTILINGUALPRESS_AUTOLOAD_DONE' ) ) {
	return;
}

/**
 * Flag to prevent registering the MultilingualPress autoload function more than once.
 *
 * @since 3.0.0
 *
 * @var bool
 */
define( 'MULTILINGUALPRESS_AUTOLOAD_DONE', true );

spl_autoload_register( function ( $fqn ) {

	if ( 0 === strpos( $fqn = ltrim( $fqn, '\\' ), __NAMESPACE__ . '\\' ) ) {
		$file_path = str_replace( '\\', '/', __DIR__ . substr( $fqn, strlen( __NAMESPACE__ ) ) ) . '.php';
		if ( is_readable( $file_path ) ) {
			/** @noinspection PhpIncludeInspection
			 * The desired plugin file.
			 */
			require_once $file_path;
		}
	}
}, true, true );
