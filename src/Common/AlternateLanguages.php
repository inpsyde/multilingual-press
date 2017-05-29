<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common;

use Inpsyde\MultilingualPress\API\Translations;
use Inpsyde\MultilingualPress\Module\Redirect\NoredirectStorage;

/**
 * Alternate languages data object.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
class AlternateLanguages implements \IteratorAggregate {

	/**
	 * @var string[]
	 */
	private $data;

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
	 * Returns an iterator object for the alternate languages data array.
	 *
	 * @sinde 3.0.0
	 *
	 * @return \Traversable Iterator object with language HTTP codes as keys and URLs as values.
	 */
	public function getIterator() {

		$this->ensure_data();

		return new \ArrayIterator( $this->data );
	}

	/**
	 * Takes care that the alternate languages data is available for use.
	 *
	 * @return void
	 */
	private function ensure_data() {

		if ( isset( $this->data ) ) {
			return;
		}

		$translations = $this->translations->get_translations( [
			'include_base' => true,
		] );

		$regexp = '/(\?|&)' . NoredirectStorage::KEY . '=/';

		$data = [];

		foreach ( $translations as $translation ) {
			$url = $translation->remote_url();
			if ( $url ) {
				$language = $translation->language();

				if ( preg_match( $regexp, $url ) ) {
					$url = remove_query_arg( NoredirectStorage::KEY, $url );
				}

				$data[ $language->name( 'http_code' ) ] = $url;
			}
		}

		$this->data = $data;
	}
}
