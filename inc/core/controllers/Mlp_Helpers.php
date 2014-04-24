<?php

/**
 * Module Name:    Multilingual Press Helpers
 * Description:    Several helper functions
 * Author:        Inpsyde GmbH
 * Version:        0.8
 * Author URI:    http://inpsyde.com
 *
 * Changelog
 *
 * 0.8
 * - added show_current_blog / added params for show_linked_elements
 * - removed get_linked_elements request for is_home
 * - added filter to remove the static ?noredirect= parameter
 *
 * 0.7
 * - Added mlp_get_interlinked_permalinks
 * - Added mlp_get_blog_language
 *
 * 0.6
 * - Codexified
 * - Fixed Notices
 *
 * 0.5.2a
 * - Initial Commit
 *
 */
class Mlp_Helpers {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private static $api;

	/**
	 * @var string
	 */
	public static $link_table = '';

	/**
	 * Check whether redirect = on for specific blog
	 *
	 * @param    bool $blogid | blog to check setting for
	 * @return    bool $redirect
	 */
	static public function is_redirect( $blogid = FALSE ) {

		if ( ! $blogid )
			$blogid = get_current_blog_id();

		$redirect = get_blog_option( $blogid, 'inpsyde_multilingual_redirect' );

		return (bool) $redirect;
	}

	/**
	 * Get the language set by MlP.
	 *
	 * @param  bool $short
	 * @return string the language code
	 */
	static public function get_current_blog_language( $short = FALSE ) {

		// Get all registered blogs
		$languages = get_site_option( 'inpsyde_multilingual' );

		// Get current blog
		$blogid = get_current_blog_id();

		// If this blog is in a language
		if ( ! isset ( $languages[ $blogid ][ 'lang' ] ) )
			return '';

		if ( ! $short )
			return $languages[ $blogid ][ 'lang' ];

		return strtok( $languages[ $blogid ][ 'lang' ], '_' );
	}

	/**
	 * Load the languages set for each blog
	 *
	 * @since   0.1
	 * @static
	 * @access  public
	 * @uses    get_site_option, get_blog_option, get_current_blog_id, format_code_lang
	 * @param   $not_related | filter out non-related blogs? By default
	 * @return  array $options
	 */
	public static function get_available_languages( $not_related = FALSE ) {

		$related_blogs = array ();

		// Get all registered blogs
		$languages = get_site_option( 'inpsyde_multilingual' );

		if ( empty ( $languages ) )
			return array ();

		// Do we need related blogs only?
		if ( FALSE === $not_related )
			$related_blogs = (array) get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

		// No related blogs? Leave here.
		if ( empty ( $related_blogs ) && FALSE === $not_related )
			return array ();

		$options = array ();

		// Loop through blogs
		foreach ( $languages as $language_blogid => $language_data ) {

			// no blogs with a link to other blogs
			if ( '-1' === $language_data[ 'lang' ] )
				continue;

			// Filter out blogs that are not related
			if ( ! $not_related && ! in_array( $language_blogid, $related_blogs ) )
				continue;

			$lang = $language_data[ 'lang' ];

			$options[ $language_blogid ] = $lang;
		}

		return $options;
	}

	/**
	 * Load the alternative title
	 * set for each blog language
	 *
	 * @since   0.5.3b
	 * @static
	 * @access  public
	 * @uses    get_site_option
	 * @param   bool $related Filter out unrelated blogs?
	 * @return  array $options
	 */
	static public function get_available_languages_titles( $related = TRUE ) {

		$api  = self::get_language_api();
		$blog = $related ? get_current_blog_id() : 0;

		return $api->get_site_languages( $blog );
	}

	/**
	 * Get native name by ISO-639-1 code.
	 *
	 * @param string $iso Language code like "en" or "de"
	 * @return string
	 */
	public static function get_lang_by_iso( $iso ) {

		$api = self::get_language_api();

		return $api->get_lang_data_by_iso( $iso );
	}

	/**
	 * Get the element ID
	 * in other blogs for
	 * the selected element
	 *
	 * @param   int    $element_id ID of the selected element
	 * @param   string $type       | type of the selected element
	 * @param   int    $blog_id    ID of the selected blog
	 * @global         $wpdb       wpdb WordPress Database Wrapper
	 * @return  array $elements
	 */
	static public function load_linked_elements( $element_id = 0,
		/** @noinspection PhpUnusedParameterInspection */
												 $type = '',
												 $blog_id = 0
	) {
		global $wpdb;

		static $cache = array ();

		// if no element id is provides, use WP default
		if ( ! $element_id )
			$element_id = get_the_ID();

		// If no ID is provided, get current blogs' ID
		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		if ( isset ( $cache [ $blog_id ] ) && isset ( $cache [ $blog_id ][ $element_id ] ) )
			return $cache [ $blog_id ][ $element_id ];

		// Get linked elements @formatter:off
		$query = $wpdb->prepare(
			'SELECT t.ml_blogid, t.ml_elementid
			FROM ' . self::$link_table . ' s
			INNER JOIN ' . self::$link_table . ' t
			ON s.ml_source_blogid = t.ml_source_blogid && s.ml_source_elementid = t.ml_source_elementid
			WHERE s.ml_blogid = %d && s.ml_elementid = %d',
			$blog_id,
			$element_id
		);
		// @formatter:on
		$results = $wpdb->get_results( $query );

		// No linked elements? Adios.
		if ( 0 >= count( $results ) )
			return array ();

		// Walk results
		$elements = array ();

		foreach ( $results as $result ) {
			if ( $blog_id != $result->ml_blogid )
				$elements[ $result->ml_blogid ] = ( int ) $result->ml_elementid;
		}

		$cache [ $blog_id ][ $element_id ] = $elements;

		// Return linked elements in other blogs
		// as an array containing blog_id => element_id
		return $elements;
	}

	/**
	 * Get the element ID in other blogs for the selected element
	 * with additional informations
	 *
	 * @global    $wpdb wpdb WordPress Database Wrapper
	 * @param int $element_id
	 * @return  array $elements
	 */
	public static function get_interlinked_permalinks( $element_id = 0 ) {
		global $wpdb;

		// if no element id is provides, use WP default
		if ( 0 == $element_id )
			$element_id = get_the_ID();

		$blog_id = get_current_blog_id();

		// Get linked elements
		$results = $wpdb->get_results(
						$wpdb->prepare(
							 'SELECT t.ml_blogid, t.ml_elementid
							 FROM ' . self::$link_table . ' s
				 INNER JOIN ' . self::$link_table . ' t
				 	ON s.ml_source_blogid = t.ml_source_blogid && s.ml_source_elementid = t.ml_source_elementid
				 WHERE s.ml_blogid = %d && s.ml_elementid = %d',
								 $blog_id,
								 $element_id
						)
		);

		// No linked elements? Adios.
		if ( 0 >= count( $results ) )
			return array ();

		// Walk results
		$elements = array ();

		foreach ( $results as $r ) {
			if ( $blog_id != $r->ml_blogid ) {

				switch_to_blog( $r->ml_blogid );

				$elements[ $r->ml_blogid ] = array (
					'post_id'        => ( int ) $r->ml_elementid,
					'post_title'     => get_the_title( $r->ml_elementid ),
					'permalink'      => get_permalink( $r->ml_elementid ),
					'flag'           => self::get_language_flag( $r->ml_blogid ),
					/* 'lang' is the old entry, language_short the first part
					 * until the '_', long the complete language tag.
					 */
					'lang'           => self::get_blog_language( $r->ml_blogid ),
					'language_short' => self::get_blog_language( $r->ml_blogid ),
					'language_long'  => self::get_blog_language( $r->ml_blogid, FALSE ),
				);

				restore_current_blog();
			}
		}

		return $elements;
	}

	/**
	 * function for custom plugins to get activated on all language blogs
	 *
	 * @param   int    $element_id ID of the selected element
	 * @param   string $type       type of the selected element
	 * @param   int    $blog_id    ID of the selected blog
	 * @param   string $hook
	 * @param   mixed  $param
	 * @return  WP_Error|NULL
	 */
	static public function run_custom_plugin( $element_id, $type,
		/** @noinspection PhpUnusedParameterInspection */
											  $blog_id,
											  $hook, $param
	) {

		if ( empty( $element_id ) )
			return new WP_Error( 'mlp_empty_custom_element', __( 'Empty Element', 'multilingualpress' ) );

		if ( empty( $type ) )
			return new WP_Error( 'mlp_empty_custom_type', __( 'Empty Type', 'multilingualpress' ) );

		if ( empty ( $hook ) || ! is_callable( $hook ) )
			return new WP_Error( 'mlp_empty_custom_hook', __( 'Invalid Hook', 'multilingualpress' ) );

		// set the current element in the mlp class
		$languages    = mlp_get_available_languages();
		$current_blog = get_current_blog_id();

		if ( 0 == count( $languages ) )
			return NULL;

		foreach ( $languages as $language_id => $language_name ) {

			if ( $current_blog == $language_id )
				continue;

			switch_to_blog( $language_id );
			// custom hook
			do_action( $hook, $param );
			restore_current_blog();
		}

		return NULL;
	}

	/**
	 * Get the url of the
	 * flag from a blogid
	 *
	 * @since     0.1
	 * @access    public
	 * @uses      get_current_blog_id, get_blog_option, get_site_option
	 *            plugin_dir_path
	 * @param    int $blog_id ID of a blog
	 * @return    string url of the language image
	 */
	static public function get_language_flag( $blog_id = 0 ) {

		$url = '';

		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		// Custom flag image set?
		$custom_flag = get_blog_option( $blog_id, 'inpsyde_multilingual_flag_url' );
		if ( $custom_flag )
			return $custom_flag;

		// Get blog language code, which will make
		// part of the flags' file name, ie. "de.gif"
		$languages = get_site_option( 'inpsyde_multilingual' );

		// Is this a shortcode (i.e. "fr"), or an ISO
		// formatted language code (i.e. fr_BE) ?
		$language_code = ( 5 == strlen( $languages[ $blog_id ][ 'lang' ] ) )
			? strtolower( substr( $languages[ $blog_id ][ 'lang' ], 3, 2 ) )
			: substr( $languages[ $blog_id ][ 'lang' ], 0, 2 );

		$path = plugin_dir_path( self::get_plugin_main_dir() ) . 'flags/' . $language_code . '.gif';

		// Check for existing file
		if ( '' != $language_code && file_exists( $path ) )
			$url = self::get_flag_dir_url() . $language_code . '.gif';

		return $url;
	}

	/**
	 * Get the url of the
	 * flag from a blogid
	 *
	 * @since     0.7
	 * @access    public
	 * @uses      get_current_blog_id, get_site_option
	 * @param    int $blog_id ID of a blog
	 * @param  bool  $short   Return only the first part of the language code.
	 * @return    string Second part of language identifier
	 */
	static public function get_blog_language( $blog_id = 0, $short = TRUE ) {

		static $languages;

		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		if ( empty ( $languages ) )
			$languages = get_site_option( 'inpsyde_multilingual' );

		if ( empty ( $languages )
			or empty ( $languages[ $blog_id ] )
			or empty ( $languages[ $blog_id ][ 'lang' ] )
		)
			return '';

		if ( ! $short )
			return $languages[ $blog_id ][ 'lang' ];

		return strtok( $languages[ $blog_id ][ 'lang' ], '_' );
	}

	// not used yet
	/*
	static public function get_blog_language_object( $blog_id = 0 ) {

		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		// $languages = get_site_option( 'inpsyde_multilingual' );
	}
	*/

	/**
	 * Get the linked elements and display them as a list
	 * flag from a blogid
	 *
	 * @param    array $args
	 * @return    string output of the bloglist
	 */
	static public function show_linked_elements( $args ) {

		global $wp_query;

		$output          = '';
		$languages       = mlp_get_available_languages();
		$language_titles = mlp_get_available_languages_titles();

		if ( ! ( 0 < count( $languages ) ) )
			return $output;

		// returns NULL if there is no post, get_the_ID() throws a notice,
		// if we don' check this before.
		$default_post = get_post();

		if ( $default_post )
			$current_element_id = get_the_ID();
		elseif ( ! empty ( $wp_query->queried_object ) && ! empty ( $wp_query->queried_object->ID ) )
			$current_element_id = $wp_query->queried_object->ID;
		else
			$current_element_id = 0;

		$linked_elements = array ();

		// double check to avoid issues with a static front page.
		if ( ! is_front_page() && ! is_home() && is_singular() )
			$linked_elements = mlp_get_linked_elements( $current_element_id );

		$defaults = array (
			'link_text' => 'text', 'echo' => TRUE,
			'sort'      => 'blogid', 'show_current_blog' => FALSE,
		);

		$params = wp_parse_args( $args, $defaults );

		// Show current blog
		if ( ! empty ( $params[ 'show_current_blog' ] ) )
			$languages[ get_current_blog_id() ] = mlp_get_current_blog_language();

		if ( 'blogid' == $params[ 'sort' ] )
			ksort( $languages );
		else
			asort( $languages );

		$output .= '<div class="mlp_language_box"><ul>';
		$title = mlp_get_available_languages_titles();

		foreach ( $languages as $language_blog => $language_string ) {

			$current_language = mlp_get_current_blog_language();
			if ( $current_language == $language_string && $params[ 'show_current_blog' ] == FALSE )
				continue;

			// Get params
			$flag       = mlp_get_language_flag( $language_blog );
			$dimensions = self::get_flag_dimension_attributes( $flag );
			$flag_img   = '<img src="' . $flag . '" alt="' . $languages[ $language_blog ] . '" title="' . $title[ $language_blog ] . '"' . $dimensions . ' />';


			// Display type
			if ( 'flag' == $params[ 'link_text' ] && '' != $flag )
				$display = $flag_img;
			else if ( 'text' == $params[ 'link_text' ] && ! empty( $language_titles[ $language_blog ] ) )
				$display = $language_titles[ $language_blog ];
			else if ( 'text_flag' == $params[ 'link_text' ] ) {
				$display = $flag_img;
				if ( ! empty( $language_titles[ $language_blog ] ) )
					$display .= ' ' . $language_titles[ $language_blog ];
			}
			else
				$display = $languages[ $language_blog ];

			$class = ( get_current_blog_id() == $language_blog ) ? 'id="mlp_current_locale"' : '';

			// set element to 0 to avoid empty element
			if ( ! isset( $linked_elements[ $language_blog ] ) )
				$linked_elements[ $language_blog ] = 0;

			// Check post status
			$post = $linked_elements[ $language_blog ] > 0 ? get_blog_post( $language_blog, $linked_elements[ $language_blog ] ) : '';

			$link =
				( is_single() || is_page() || is_home() ) &&
				isset( $post->post_status ) &&
				( 'publish' === $post->post_status || ( 'private' === $post->post_status && is_super_admin() ) )
					?
					// get element link if available
					get_blog_permalink( $language_blog, $linked_elements[ $language_blog ] )
					:
					// link to siteurl of blog
					get_site_url( $language_blog, '/' );

			// apply filter to help others to change the link
			$link = apply_filters( 'mlp_linked_element_link', $link, $language_blog, $linked_elements[ $language_blog ] );

			// Output link elements
			$output .= '<li ' . ( $current_language == $language_string ? 'class="current"' : '' ) . '><a rel="alternate" hreflang="' . self::get_blog_language( $language_blog ) . '" ' . $class . ' href="' . $link . '">' . $display . '</a></li>';
		}
		$output .= '</ul></div>';

		return $output;
	}

	/**
	 * Get HTML attributes width and height for a flag image.
	 *
	 * @param  string $flag_url
	 * @return string
	 */
	private static function get_flag_dimension_attributes( $flag_url ) {
		if ( 0 !== strpos( $flag_url, self::get_flag_dir_url() ) )
			return '';

		return ' width="16" height="11"';
	}

	/**
	 * Get default directory for flags.
	 *
	 * @return string
	 */
	private static function get_flag_dir_url() {
		return plugins_url( 'flags/', self::get_plugin_main_dir() );
	}

	/**
	 * Temporary fix to get the main plugin directory.
	 *
	 * @return string
	 */
	private static function get_plugin_main_dir() {
		return dirname( dirname( dirname( __FILE__ ) ) );
	}

	/**
	 * @return Mlp_Language_Api
	 */
	private static function get_language_api() {

		if ( is_a( self::$api, 'Mlp_Language_Api_Interface' ) )
			return self::$api;

		self::$api = apply_filters( 'mlp_language_api', NULL );

		if ( ! is_a( self::$api, 'Mlp_Language_Api_Interface' ) )
			self::$api = new Mlp_Language_Api( new Inpsyde_Property_List, self::$link_table );

		return self::$api;
	}
}
