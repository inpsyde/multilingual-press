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
	const FILTER_HREFLANG_HTTP_HEADER = 'multilingualpress.hreflang_http_header';

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_RENDER_HREFLANG = 'multilingualpress.render_hreflang';

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
	 * @return void|mixed
	 */
	public function render( ...$args ) {

		$translations = $this->alternate_languages->getIterator();

		/**
		 * Filters if the hreflang links should be rendered.
		 *
		 * @since 3.0.0
		 *
		 * @param bool     $render       Whether or not hreflang links should be rendered.
		 * @param string[] $translations The available translations to be used for hreflang links.
		 * @param int      $type         The output type.
		 */
		if ( ! apply_filters(
			self::FILTER_RENDER_HREFLANG,
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
			$header = (string) apply_filters( self::FILTER_HREFLANG_HTTP_HEADER, $header, $language, $url );
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
