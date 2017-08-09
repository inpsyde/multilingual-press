<?php # -*- coding: utf-8 -*-
/**
 * Find best alternative for given content
 *
 * @version    2014.09.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Language_Negotiation implements Mlp_Language_Negotiation_Interface {

	/**
	 * @type Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @var float
	 */
	private $language_only_priority_factor;

	/**
	 * @type Mlp_Accept_Header_Parser_Interface
	 */
	private $parser;

	/**
	 * @param Mlp_Language_Api_Interface         $language_api
	 * @param Mlp_Accept_Header_Parser_Interface $parser
	 */
	public function __construct(
		Mlp_Language_Api_Interface         $language_api,
		Mlp_Accept_Header_Parser_Interface $parser
	) {

		$this->language_api = $language_api;
		$this->parser = $parser;

		/**
		 * Filters the factor used to compute the priority of language-only matches. This has to be between 0 and 1.
		 *
		 * @see   get_user_priority()
		 * @since 2.4.8
		 *
		 * @param float $factor The factor used to compute the priority of language-only matches.
		 */
		$factor = (float) apply_filters( 'multilingualpress.language_only_priority_factor', .8 );
		$factor = min( 1, $factor );
		$factor = max( 0, $factor );

		$this->language_only_priority_factor = $factor;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_redirect_match( array $args = array() ) {

		/**
		 * Filters the allowed status for posts to be included as possible redirect targets.
		 *
		 * @since 2.8.0
		 *
		 * @param string[] $post_status Allowed post status.
		 */
		$post_status = (array) apply_filters( 'multilingualpress.redirect_post_status', array(
			'publish',
		) );

		$translations = $this->language_api->get_translations( array_merge( array(
			'include_base' => TRUE,
			'post_status'  => $post_status,
		), $args ) );

		if ( empty ( $translations ) )
			return $this->get_fallback_match();

		$possible = $this->get_possible_matches( $translations );

		if ( empty ( $possible ) )
			return $this->get_fallback_match();

		uasort( $possible, array ( $this, 'sort_priorities' ) );

		return array_pop( $possible );
	}

	/**
	 * @param array $translations
	 * @return array
	 */
	private function get_possible_matches( Array $translations ) {

		$user = $this->parse_accept_header( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] );
		if ( empty( $user ) ) {
			return array();
		}

		$matches = array();

		/** @var Mlp_Translation $translation */
		foreach ( $translations as $site_id => $translation ) {
			$this->collect_matches( $matches, $site_id, $translation, $user );
		}

		/**
		 * Filters the possible redirect target objects.
		 *
		 * @since 2.7.0
		 *
		 * @param array[]           $matches      Possible redirect targets.
		 * @param Mlp_Translation[] $translations Translation objects.
		 */
		return (array) apply_filters( 'multilingualpress.redirect_targets', $matches, $translations );
	}

	/**
	 * @return array
	 */
	private function get_fallback_match() {

		return array (
			'priority'   => 0,
			'url'        => '',
			'language'   => '',
			'site_id'    => 0,
			'content_id' => 0,
		);
	}

	/**
	 * @param  array           $possible
	 * @param  int             $site_id
	 * @param  Mlp_Translation $translation
	 * @param  array           $user
	 * @return void
	 */
	private function collect_matches(
		Array           &$possible,
		                $site_id,
		Mlp_Translation $translation,
		Array           $user
	) {

		$language      = $translation->get_language();
		$user_priority = $this->get_user_priority( $language, $user );

		if ( 0 === $user_priority )
			return;

		$url = $translation->get_remote_url();

		if ( empty ( $url ) )
			return;

		$combined_value   = $language->get_priority() * $user_priority;
		$possible[]       = array (
			'priority'   => $combined_value,
			'url'        => $url,
			'language'   => $language->get_name( 'http' ),
			'site_id'    => $site_id,
			'content_id' => $translation->get_target_content_id(),
		);
	}

	/**
	 * Helper to sort URLs by priority.
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return int
	 */
	private function sort_priorities( $a, $b ) {

		if ( $a[ 'priority' ] === $b[ 'priority' ] )
			return 0;

		return ( $a[ 'priority' ] < $b[ 'priority' ] ) ? -1 : 1;
	}

	/**
	 * @param Mlp_Language_Interface $language
	 * @param array                  $user
	 * @return float The user priority
	 */
	private function get_user_priority( Mlp_Language_Interface $language, Array $user ) {

		$lang_http = $language->get_name( 'http_name' );
		$lang_http = strtolower( $lang_http );

		if ( isset ( $user[ $lang_http ] ) )
			return $user[ $lang_http ];

		$lang_short = $language->get_name( 'language_short' );
		$lang_short = strtolower( $lang_short );

		if ( isset ( $user[ $lang_short ] ) )
			return $this->language_only_priority_factor * $user[ $lang_short ];

		return 0;
	}

	/**
	 * Inspect HTTP_ACCEPT_LANGUAGE and parse priority parameters.
	 *
	 * @param string $accept_header Accept header string
	 * @return array
	 */
	private function parse_accept_header( $accept_header ) {

		$fields = $this->parser->parse( $accept_header );

		if ( empty ( $fields ) )
			return $fields;

		$out = array ();

		foreach ( $fields as $name => $priority ) {
			$name = strtolower( $name );

			$out[ $name ] = $priority;

			$short = $this->get_short_form( $name );

			if ( $short && ( $short !== $name ) && ! isset ( $out[ $short ] ) )
				$out[ $short ] = $priority;
		}

		return $out;
	}

	/**
	 * Get the first characters of a language code until an '-'.
	 *
	 * @param  string $long
	 * @return string
	 */
	private function get_short_form( $long ) {

		if ( ! strpos( $long, '-' ) )
			return '';

		return strtok( $long, '-' );
	}
}
