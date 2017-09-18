<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\FrontEnd;

use Inpsyde\MultilingualPress\Common\AlternateLanguages;

/**
 * Alternate language HTML link tag renderer implementation.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd
 * @since   3.0.0
 */
final class AlternateLanguageHTMLLinkTagRenderer implements AlternateLanguageRenderer {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_HREFLANG = 'multilingualpress.hreflang_html_link_tag';

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
	 * Renders all alternate languages as HTML link tags into the HTML head.
	 *
	 * @since   3.0.0
	 * @wp-hook wp_head
	 *
	 * @param array ...$args Optional arguments.
	 *
	 * @return void
	 */
	public function render( ...$args ) {

		$translations = iterator_to_array( $this->alternate_languages );

		/**
		 * Filters if the hreflang links should be rendered.
		 *
		 * @since 2.8.0
		 * @since 3.0.0 Add $type argument.
		 *
		 * @param bool     $render       Whether or not hreflang links should be rendered.
		 * @param string[] $translations The available translations to be used for hreflang links.
		 * @param int      $type         The output type.
		 */
		if ( ! apply_filters(
			self::FILTER_RENDER,
			count( $translations ) > 1,
			$translations,
			$this->type()
		) ) {
			return;
		}

		$tags = [
			'link' => [
				'href'     => true,
				'hreflang' => true,
				'rel'      => true,
			],
		];

		foreach ( $translations as $language => $url ) {
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
			$html_link_tag = (string) apply_filters( self::FILTER_HREFLANG, $html_link_tag, $language, $url );

			echo wp_kses( $html_link_tag, $tags );
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

		return self::TYPE_HTML_LINK_TAG;
	}
}
