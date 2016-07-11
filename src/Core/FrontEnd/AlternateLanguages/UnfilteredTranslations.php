<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages;

use Mlp_Language_Api_Interface;

/**
 * Translation data access and nonpersistent cache.
 *
 * @package Inpsyde\MultilingualPress\Core\FrontEnd\AlternateLanguages
 * @since   3.0.0
 */
class UnfilteredTranslations implements Translations {

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
	public function get() {

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

		foreach ( $translations as $translation ) {
			$url = $translation->get_remote_url();
			if ( ! $url ) {
				continue;
			}

			$this->translations[ $translation->get_language()->get_name( 'http' ) ] = $url;
		}

		return $this->translations;
	}
}
