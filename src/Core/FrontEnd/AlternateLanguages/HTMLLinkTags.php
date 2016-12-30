<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages;

use Inpsyde\MultilingualPress\API\Translations;

/**
 * Alternate language HTML link tags.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages
 * @since   3.0.0
 */
class HTMLLinkTags {

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations $translations Translations API object.
	 */
	public function __construct( Translations $translations ) {

		$this->translations = $translations;
	}

	/**
	 * Renders an alternate language HTML link tag for each available translation into the HTML head.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_head
	 *
	 * @return bool Whether or not headers have been sent.
	 */
	public function render() {

		$translations = $this->translations->get_unfiltered_translations();
		if ( ! $translations ) {
			return false;
		}

		array_walk( $translations, function ( $url, $language ) {

			$html_link_tag = sprintf(
				'<link rel="alternate" hreflang="%1$s" href="%2$s">',
				esc_attr( $language ),
				esc_url( $url )
			);

			/**
			 * Filters the output of the hreflang links in the HTML head.
			 *
			 * @since 3.0.0
			 *
			 * @param string $html_link_tag Alternate language HTML link tag.
			 * @param string $language      HTTP language code (e.g., "en-US").
			 * @param string $url           Target URL.
			 */
			echo apply_filters( 'multilingualpress.hreflang_html_link_tag', $html_link_tag, $language, $url );
		} );

		return true;
	}
}
