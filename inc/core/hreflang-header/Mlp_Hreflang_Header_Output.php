<?php
/**
 * Send headers for alternative language representations.
 *
 * @link https://support.google.com/webmasters/answer/189077?hl=en
 * @version 2014.09.20
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */


class Mlp_Hreflang_Header_Output {

	/**
	 * @type Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @type array
	 */
	private $translations = array ();

	/**
	 * @param Mlp_Language_Api_Interface $language_api
	 */
	public function __construct( Mlp_Language_Api_Interface $language_api ) {

		$this->language_api = $language_api;
	}

	public function wp_head() {

		$translations = $this->get_translations();

		if ( empty ( $translations ) )
			return;

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $translations as $lang => $url )
			printf(
				'<link rel="alternate" hreflang="%1$s" href="%2$s" />',
				$lang,
				$url
			);
	}

	public function http_header() {

		$translations = $this->get_translations();

		if ( empty ( $translations ) )
			return;

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $translations as $lang => $url ) {
			$header = sprintf(
				'Link: <%1$s>; rel="alternate"; hreflang="%2$s"',
				$url,
				$lang
			);
			header( $header, FALSE );
		}
	}

	/**
	 * Query the language API for translations and cache the result.
	 *
	 * @return array
	 */
	private function get_translations() {

		if ( array ( 'failed' ) === $this->translations )
			return array();

		$translations = $this->language_api->get_translations();

		if ( empty ( $translations ) ) {
			$this->translations = array ( 'failed' );
			return array();
		}

		$prepared = array();

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $translations as $translation ) {

			$lang = $translation->get_language()->get_name( 'http' );
			$url  = $translation->get_remote_url();

			if ( ! empty ( $url ) )
				$prepared[ $lang ] = $url;
		}

		if ( empty ( $prepared ) ) {
			$this->translations = array ( 'failed' );
			return array();
		}

		$this->translations = $prepared;

		return $this->translations;
	}
}