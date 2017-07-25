<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Module\Redirect\NoredirectPermalinkFilter;

/**
 * Alternate languages data object.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
class AlternateLanguages implements \IteratorAggregate {

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
		if ( $translations ) {
			$regexp = '/(\?|&)' . NoredirectPermalinkFilter::QUERY_ARGUMENT . '=/';

			foreach ( $translations as $translation ) {
				$url = $translation->remote_url();
				if ( $url ) {
					if ( preg_match( $regexp, $url ) ) {
						$url = remove_query_arg( NoredirectPermalinkFilter::QUERY_ARGUMENT, $url );
					}

					$urls[ $translation->language()->name( 'http_code' ) ] = $url;
				}
			}
		}

		/**
		 * Filters the available translations to be used for hreflang links.
		 *
		 * @since 2.7.0
		 *
		 * @param string[] $translations The available translations to be used for hreflang links.
		 */
		$this->urls = (array) apply_filters( 'multilingualpress.hreflang_translations', $urls );
	}
}
