<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\FrontEnd;

use Inpsyde\MultilingualPress\Common\AlternateLanguages;
use Inpsyde\MultilingualPress\Module\Redirect\NoredirectStorage;

/**
 * Alternate language HTML link tags.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd
 * @since   3.0.0
 */
class AlternateLanguageHTMLLinkTags {

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
	 * Renders an alternate language HTML link tag for each available translation into the HTML head.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_head
	 *
	 * @return void
	 */
	public function render() {

		$regexp = '/(\?|&)' . NoredirectStorage::KEY . '=/';

		foreach ( $this->alternate_languages->getIterator() as $language => $url ) {
			if ( preg_match( $regexp, $url ) ) {
				$url = remove_query_arg( NoredirectStorage::KEY, $url );
			}

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
		}
	}
}
