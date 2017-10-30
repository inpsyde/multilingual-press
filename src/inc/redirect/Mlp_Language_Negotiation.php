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
		Mlp_Language_Api_Interface $language_api,
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

		$targets = $this->get_redirect_targets( $args );
		if ( ! $targets ) {
			return $this->get_fallback_match();
		}

		foreach ( $targets as $key => $target ) {
			if ( empty( $target['user_priority'] ) || 0.0 === (float) $target['user_priority'] ) {
				unset( $targets[ $key ] );
			}
		}

		if ( ! $targets ) {
			return $this->get_fallback_match();
		}

		uasort( $targets, array( $this, 'sort_combined_priorities' ) );

		return reset( $targets );
	}

	/**
	 * Returns the redirect target data for all available language versions.
	 *
	 * @since 2.10.0
	 *
	 * @param array $args Optional. Arguments required to determine the redirect targets. Defaults to empty array.
	 *
	 * @return array[] array of redirect targets.
	 */
	public function get_redirect_targets( array $args = array() ) {

		$current_site_id = get_current_blog_id();

		$user_languages = empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] )
			? array()
			: $this->parse_accept_header( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );

		$translations = $this->get_translations( $args );

		$targets = array();

		/** @var Mlp_Translation $translation */
		foreach ( $translations as $site_id => $translation ) {
			$this->collect_matches( $targets, $site_id, $translation, $user_languages, $current_site_id );
		}

		/**
		 * Filters the possible redirect target objects.
		 *
		 * @since 2.7.0
		 *
		 * @param array[]           $targets      Possible redirect targets.
		 * @param Mlp_Translation[] $translations Translation objects.
		 */
		$targets = (array) apply_filters( 'multilingualpress.redirect_targets', $targets, $translations );
		if ( ! $targets ) {
			return array();
		}

		uasort( $targets, array( $this, 'sort_priorities' ) );

		return $targets;
	}

	/**
	 * Returns all translations according to the given arguments.
	 *
	 * @param array $args Arguments required to fetch the translations.
	 *
	 * @return Mlp_Translation[] An array with site IDs as keys and Mlp_Translation objects as values.
	 */
	private function get_translations( array $args = array() ) {

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
			'include_base' => true,
			'post_status'  => $post_status,
		), $args ) );
		if ( ! $translations ) {
			return array();
		}

		/**
		 * @var Mlp_Translation $translation
		 */
		foreach ( $translations as $key => $translation ) {
			if ( ! $translation->get_remote_url() ) {
				unset( $translations[ $key ] );
			}
		}

		return $translations;
	}

	/**
	 * @return array
	 */
	private function get_fallback_match() {

		return array(
			'priority'      => 0,
			'user_priority' => 0.0,
			'url'           => '',
			'language'      => '',
			'site_id'       => 0,
			'content_id'    => 0,
		);
	}

	/**
	 * @param  array           $possible
	 * @param  int             $site_id
	 * @param  Mlp_Translation $translation
	 * @param  array           $user
	 * @param  int             $current_site_id
	 * @return void
	 */
	private function collect_matches(
		array &$possible,
		$site_id,
		Mlp_Translation $translation,
		array $user,
		$current_site_id
	) {

		$language = $translation->get_language();

		$user_priority = $this->get_user_priority( $language, $user );

		$url = $translation->get_remote_url();

		$target = array(
			'priority'      => $language->get_priority(),
			'user_priority' => $user_priority,
			'url'           => $url,
			'language'      => $language->get_name( 'http' ),
			'site_id'       => $site_id,
			'content_id'    => $translation->get_target_content_id(),
		);

		/**
		 * Filters the redirect URL.
		 *
		 * @param string $url             Redirect URL.
		 * @param array  $target          Redirect target. {
		 *                                    'priority' => int
		 *                                    'url'      => string
		 *                                    'language' => string
		 *                                    'site_id'  => int
		 *                                }
		 * @param int    $current_site_id Current site ID.
		 */
		$url = (string) apply_filters( 'mlp_redirect_url', $target['url'], $target, $current_site_id );
		if ( ! empty( $url ) ) {
			$target['url'] = $url;

			$possible[] = $target;
		}
	}

	/**
	 * Helper to sort URLs by combined priority.
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return int
	 */
	private function sort_combined_priorities( $a, $b ) {

		$a = $a['priority'] * $a['user_priority'];

		$b = $b['priority'] * $b['user_priority'];

		if ( $a === $b ) {
			return 0;
		}

		return ( $a < $b ) ? 1 : -1;
	}

	/**
	 * Helper to sort URLs by priority.
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return int
	 */
	private function sort_priorities( $a, $b ) {

		if ( $a['priority'] === $b['priority'] ) {
			return 0;
		}

		return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
	}

	/**
	 * @param Mlp_Language_Interface $language
	 * @param array                  $user
	 * @return float The user priority
	 */
	private function get_user_priority( Mlp_Language_Interface $language, array $user ) {

		$lang_http = $language->get_name( 'http_name' );
		$lang_http = strtolower( $lang_http );

		if ( isset( $user[ $lang_http ] ) ) {
			return $user[ $lang_http ];
		}

		$lang_short = $language->get_name( 'language_short' );
		$lang_short = strtolower( $lang_short );

		if ( isset( $user[ $lang_short ] ) ) {
			return $this->language_only_priority_factor * $user[ $lang_short ];
		}

		return 0.0;
	}

	/**
	 * Inspect HTTP_ACCEPT_LANGUAGE and parse priority parameters.
	 *
	 * @param string $accept_header Accept header string
	 * @return array
	 */
	private function parse_accept_header( $accept_header ) {

		$fields = $this->parser->parse( $accept_header );

		if ( empty( $fields ) ) {
			return $fields;
		}

		$out = array();

		foreach ( $fields as $name => $priority ) {
			$name = strtolower( $name );

			$out[ $name ] = $priority;

			$short = $this->get_short_form( $name );

			if ( $short && ( $short !== $name ) && ! isset( $out[ $short ] ) ) {
				$out[ $short ] = $priority;
			}
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

		if ( ! strpos( $long, '-' ) ) {
			return '';
		}

		return strtok( $long, '-' );
	}
}
