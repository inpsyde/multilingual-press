<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\FrontEnd;

use Inpsyde\MultilingualPress\Common\AlternateLanguages;

/**
 * Alternate language HTTP header renderer implementation.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd
 * @since   3.0.0
 */
final class AlternateLanguageHTTPHeaderRenderer implements AlternateLanguageRenderer {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_HREFLANG = 'multilingualpress.hreflang_http_header';

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_RENDER = 'multilingualpress.render_hreflang';

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
	 * Renders all available alternate languages as Link HTTP headers.
	 *
	 * @since   3.0.0
	 * @wp-hook template_redirect
	 *
	 * @param array ...$args Optional arguments.
	 *
	 * @return void
	 */
	public function render( ...$args ) {

		$translations = iterator_to_array( $this->alternate_languages );

		/** This filter is documented in src/Core/FrontEnd/AlternateLanguageHTMLLinkTagRenderer.php */
		if ( ! apply_filters(
			self::FILTER_RENDER,
			count( $translations ) > 1,
			$translations,
			$this->type()
		) ) {
			return;
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
			 * @since 3.0.0
			 *
			 * @param string $header   Alternate language HTTP header.
			 * @param string $language HTTP language code (e.g., "en-US").
			 * @param string $url      Target URL.
			 */
			$header = (string) apply_filters( self::FILTER_HREFLANG, $header, $language, $url );
			if ( $header ) {
				header( $header, false );
			}
		}
	}

	/**
	 * Returns the output type.
	 *
	 * @since 3.0.0
	 *
	 * @return int The output type.
	 */
	public function type(): int {

		return self::TYPE_HTTP_HEADER;
	}
}
