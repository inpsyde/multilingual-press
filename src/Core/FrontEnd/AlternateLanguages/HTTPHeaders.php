<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages;

/**
 * Alternate language HTTP headers.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages
 * @since   3.0.0
 */
class HTTPHeaders {

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations $translations Translations access object.
	 */
	public function __construct( Translations $translations ) {

		$this->translations = $translations;
	}

	/**
	 * Sends an alternate language HTTP header for each available translation.
	 *
	 * @wp-hook template_redirect
	 *
	 * @return bool Whether or not headers have been sent.
	 */
	public function send() {

		$translations = $this->translations->get();
		if ( ! $translations ) {
			return false;
		}

		foreach ( $translations as $language => $url ) {
			$header = sprintf(
				'Link: <%1$s>; rel="alternate"; hreflang="%2$s"',
				esc_url( $url ),
				esc_attr( $language )
			);

			/**
			 * Filters the output of the hreflang links in the HTTP header.
			 *
			 * @since TODO
			 *
			 * @param string $header   Alternate language HTTP header.
			 * @param string $language HTTP language code (e.g., "en-US").
			 * @param string $url      Target URL.
			 */
			$header = (string) apply_filters( 'mlp_hreflang_http_header', $header, $language, $url );
			if ( $header ) {
				header( $header, false );
			}
		}

		return true;
	}
}
