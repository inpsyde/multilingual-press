<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages;

use Inpsyde\MultilingualPress\Common\Type\Translation;
use Mlp_Language_Api_Interface;

/**
 * Translation data access and nonpersistent cache.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages
 * @since   3.0.0
 */
final class UnfilteredTranslations implements Translations {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @var string[]
	 */
	private $translations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Mlp_Language_Api_Interface $language_api Language API object.
	 */
	public function __construct( Mlp_Language_Api_Interface $language_api ) {

		$this->language_api = $language_api;
	}

	/**
	 * Returns the translations.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Array with HTTP language codes as keys and URLs as values.
	 */
	public function to_array() {

		if ( isset( $this->translations ) ) {
			return $this->translations;
		}

		$this->translations = [];

		$translations = $this->language_api->get_translations( [
			'include_base'     => true,
			'suppress_filters' => true,
		] );
		if ( ! $translations ) {
			return $this->translations;
		}

		array_walk( $translations, function ( Translation $translation ) {

			$url = $translation->remote_url();
			if ( $url ) {
				$this->translations[ $translation->language()->name( 'http' ) ] = $url;
			}
		} );

		return $this->translations;
	}
}
