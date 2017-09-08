<?php # -*- coding: utf-8 -*-

namespace {

	if ( ! function_exists( 'apache_request_headers' ) ) :
		/**
		 * Polyfill for missing apache_request_headers() function.
		 *
		 * @see   https://stackoverflow.com/a/28000512/2117788
		 * @since 3.0.0
		 *
		 * @return string[] An associative array of all the HTTP headers in the current request.
		 */
		function apache_request_headers() {

			static $headers;
			if ( is_array( $headers ) ) {
				return $headers;
			}

			$headers = [];

			foreach ( $_SERVER as $key => $value ) {
				if ( 0 === strpos( $key, 'HTTP_' ) ) {
					$key = str_replace( ' ', '-', ucwords( str_replace( '_', ' ', strtolower( substr( $key, 5 ) ) ) ) );

					$headers[ $key ] = $value;
				}
			}

			return $headers;
		}
	endif;

}

namespace Inpsyde\MultilingualPress {

	if ( ! function_exists( __NAMESPACE__ . '\\debug') ) :
		/**
		 * Writes debug data to the error log.
		 *
		 * To enable this function, add the following line to your wp-config.php file:
		 *
		 *     define( 'MULTILINGUALPRESS_DEBUG', true );
		 *
		 * @since 3.0.0
		 *
		 * @param string $message The message to be logged.
		 *
		 * @return void
		 */
		function debug( $message ) {

			if ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG ) {
				// @codingStandardsIgnoreLine as this is a function specifically intended to be used when debugging.
				error_log( sprintf(
					'MultilingualPress: %s %s',
					date( 'H:m:s' ),
					$message
				) );
			}
		}
	endif;

}
