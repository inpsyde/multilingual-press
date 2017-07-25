<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Common\Type\Translation;

/**
 * Alternate languages data object.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
class AlternateLanguages implements \IteratorAggregate {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_TRANSLATIONS = 'multilingualpress.hreflang_translations';

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_URL = 'multilingualpress.hreflang_url';

	/**
	 * @var Translations
	 */
	private $api;

	/**
	 * @var string[]
	 */
	private $urls;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Translations $api Translations API object.
	 */
	public function __construct( Translations $api ) {

		$this->api = $api;
	}

	/**
	 * Returns an iterator object for the alternate languages data array.
	 *
	 * @sinde 3.0.0
	 *
	 * @return \Traversable Iterator object with language HTTP codes as keys and URLs as values.
	 */
	public function getIterator() {

		$this->ensure_urls();

		return new \ArrayIterator( $this->urls );
	}

	/**
	 * Takes care that the alternate language URLs are available for use.
	 *
	 * @return void
	 */
	private function ensure_urls() {

		if ( isset( $this->urls ) ) {
			return;
		}

		$urls = [];

		$translations = $this->api->get_translations( [
			'include_base' => true,
		] );
		foreach ( $translations as $translation ) {
			$url = $translation->remote_url();
			if ( $url ) {
				/**
				 * Filters the URL to be used for hreflang links.
				 *
				 * @since 3.0.0
				 *
				 * @param string      $url         The URL to be used for hreflang links.
				 * @param Translation $translation Translation object.
				 */
				$url = (string) apply_filters( self::FILTER_URL, $url, $translation );

				$urls[ $translation->language()->name( 'http_code' ) ] = $url;
			}
		}

		/**
		 * Filters the available translations to be used for hreflang links.
		 *
		 * @since 2.7.0
		 *
		 * @param string[] $translations The available translations to be used for hreflang links.
		 */
		$this->urls = (array) apply_filters( self::FILTER_TRANSLATIONS, $urls );
	}
}
