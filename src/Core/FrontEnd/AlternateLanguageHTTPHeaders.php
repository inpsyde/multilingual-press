<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\FrontEnd;

use Inpsyde\MultilingualPress\Common\AlternateLanguages;

/**
 * Alternate language HTTP headers.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd
 * @since   3.0.0
 */
class AlternateLanguageHTTPHeaders {

	/**
	 * @var AlternateLanguages
	 */
	private $alternate_languages;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param AlternateLanguages $alternate_languages Alternate languages data object.
	 */
	public function __construct( AlternateLanguages $alternate_languages ) {

		$this->alternate_languages = $alternate_languages;
	}

	/**
	 * Sends an alternate language HTTP header for each available translation.
	 *
	 * @since   3.0.0
	 * @wp-hook template_redirect
	 *
	 * @return void
	 */
	public function send() {

		foreach ( $this->alternate_languages->getIterator() as $language => $url ) {
			$header = sprintf(
				'Link: <%1$s>; rel="alternate"; hreflang="%2$s"',
				esc_url( $url ),
				esc_attr( $language )
			);

			/**
			 * Filters the output of the hreflang links in the HTTP header.
			 *
			 * @since 3.0.0
			 *
			 * @param string $header   Alternate language HTTP header.
			 * @param string $language HTTP language code (e.g., "en-US").
			 * @param string $url      Target URL.
			 */
			$header = (string) apply_filters( 'multilingualpress.hreflang_http_header', $header, $language, $url );
			if ( $header ) {
				header( $header, false );
			}
		}
	}
}
