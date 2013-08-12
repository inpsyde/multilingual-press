<?php
/**
 * Module Name:	Multilingual Press Helpers
 * Description:	Several helper functions
 * Author:		Inpsyde GmbH
 * Version:		0.8
 * Author URI:	http://inpsyde.com
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
class Mlp_Helpers extends Multilingual_Press {

	/**
	 * Check wheter redirect = on for specific blog
	 *
	 * @since	0.5.2a
	 * @static
	 * @access	public
	 * @param	int $blogid | blog to check setting for
	 * @uses	get_current_blog_id, get_blog_option
	 * @return	bool $redirect | TRUE / FALSE
	 */
	static public function is_redirect( $blogid = FALSE ) {

		$blogid = ( FALSE == $blogid ) ? get_current_blog_id() : $blogid;
		$redirect = get_blog_option( $blogid, 'inpsyde_multilingual_redirect' );

		return $redirect;
	}

	/**
	 * Get the language set by MlP.
	 *
	 * @since	0.5.2a
	 * @static
	 * @access	public
	 * @param	string $count | Lenght of string to return
	 * @uses	get_site_option, get_current_blog_id
	 * @return	string | the language code
	 */
	static public function get_current_blog_language( $count = 0 ) {

		// Get all registered blogs
		$languages = get_site_option( 'inpsyde_multilingual' );

		// Get current blog
		$blogid = get_current_blog_id();

		// If this blog is in a language
		if ( ! isset( $languages[ $blogid ][ 'lang' ] ) )
			return;

		if ( 0 == $count )
			return $languages[ $blogid ][ 'lang' ];
		else
			return substr( $languages[ $blogid ][ 'lang' ], 0, $count );

	}

	/**
	 * Load the languages set for each blog
	 *
	 * @since   0.1
	 * @static
	 * @access  public
	 * @uses	get_site_option, get_blog_option, get_current_blog_id, format_code_lang
	 * @param   $nonrelated | filter out non-related blogs? By default
	 * @return  array $options
	 */
	static function get_available_languages( $nonrelated = FALSE ) {

		$related_blogs = '';

		// Get all registered blogs
		$languages = get_site_option( 'inpsyde_multilingual' );

		if ( ! is_array( $languages ) )
			return FALSE;

		// Do we need related blogs only?
		if ( FALSE === $nonrelated )
			$related_blogs = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

		// No related blogs? Leave here.
		if ( ! is_array( $related_blogs ) && FALSE === $nonrelated )
			return;

		$options = array( );

		// Loop through blogs
		foreach ( $languages as $language_blogid => $language_data ) {

			// no blogs with a link to other blogs
			if ( '-1' === $language_data[ 'lang' ] )
				continue;

			// Filter out blogs that are not related
			if ( is_array( $related_blogs ) && ! in_array( $language_blogid, $related_blogs ) )
				continue;

			$lang = $language_data[ 'lang' ];

			// We only need the first two letters
			// of the language code, i.e. "de"
			if ( 2 !== strlen( $lang ) ) {

				$lang = substr( $lang, 0, 2 );
				if ( is_admin() ) {
					$lang = format_code_lang( $lang );
				}
			}
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
	 * @uses	get_site_option
	 * @param   bool $nonrelated Filter out non-related blogs?
	 * @return  array $options
	 */
	static public function get_available_languages_titles( $nonrelated = FALSE ) {

		$related_blogs = '';

		$languages = get_site_option( 'inpsyde_multilingual' );

		if ( FALSE === $nonrelated )
			$related_blogs = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

		if ( ! is_array( $related_blogs ) && FALSE === $nonrelated )
			return;

		if ( ! is_array( $languages ) )
			return;

		$options = array( );

		foreach ( $languages as $language_blogid => $language_data ) {

			// Filter out blogs that are not related
			if ( is_array( $related_blogs ) && ! in_array( $language_blogid, $related_blogs ) && FALSE === $nonrelated )
				continue;

			$lang = '';

			if ( isset ( $language_data[ 'text' ] ) )
				$lang = $language_data[ 'text' ];

			// I didn't write this block :/
			if ( '' == $lang ) {
				$lang = substr( $language_data[ 'lang' ], 0, 2 ); // get the first lang element
				$lang = self::get_lang_by_iso( $lang, "native" );
			}
			$options[ $language_blogid ] = $lang;
		}
		return $options;
	}

	/**
	 * Get ISO-639-2 code, English language name or native name by ISO-639-1 code.
	 *
	 * @since 1.0.4
	 * @param string $iso Two-letter code like "en" or "de"
	 * @param string $field Sub-key name: "iso_639_2", "en" or "native",
	 *               defaults to "native", "all" returns the complete list.
	 * @return boolean|array|string FALSE for unknown language codes or fields,
	 *               array for $field = 'all' and string for specific fields
	 */
	public static function get_lang_by_iso( $iso, $field = 'native' ) {

		static $lang_list = FALSE;

		if ( ! $lang_list )
			$lang_list = require_once dirname( __FILE__ ) . '/language-list.php';

		if ( FALSE !== strpos( $iso, '_' ) )
			$iso = strtok( $iso, '_' );

		if ( ! isset ( $lang_list[ $iso ] ) )
			return FALSE;

		if ( 'all' === $field )
			return $lang_list[ $iso ];

		if ( ! isset ( $lang_list[ $iso ][ $field ] ) )
			return FALSE;

		return $lang_list[ $iso ][ $field ];
	}

	/**
	 * Get the element ID
	 * in other blogs for
	 * the selected element
	 *
	 * @since   0.1
	 * @static
	 * @access  public
	 * @uses	get_current_blog_id, get_results, get_results
	 * @param   int $element_id ID of the selected element
	 * @param   string $type | type of the selected element
	 * @param   int $blog_id ID of the selected blog
	 * @global	$wpdb WordPress Database Wrapper
	 * @return  array $elements
	 */
	static public function load_linked_elements( $element_id = FALSE, $type = '', $blog_id = 0 ) {
		global $wpdb;

		// if no element id is provides, use WP default
		if ( ! $element_id )
			$element_id = get_the_ID();

		// If no ID is provided, get current blogs' ID
		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		// Get linked elements
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT t.ml_blogid, t.ml_elementid
					FROM ' . Multilingual_Press::$class_object->link_table . ' s
					INNER JOIN ' . Multilingual_Press::$class_object->link_table . ' t
					ON s.ml_source_blogid = t.ml_source_blogid && s.ml_source_elementid = t.ml_source_elementid
					WHERE s.ml_blogid = %d && s.ml_elementid = %d',
				$blog_id,
				$element_id
			)
		);

		// No linked elements? Adios.
		if ( 0 >= count( $results ) )
			return array();

		// Walk results
		$elements = array();

		foreach ( $results as $resultelement ) {
			if ( $blog_id != $resultelement->ml_blogid )
				$elements[ $resultelement->ml_blogid ] = ( int ) $resultelement->ml_elementid;
		}

		// Return linked elements in other blogs
		// as an array containing blog_id => element_id
		return $elements;
	}

	/**
	 * Get the element ID
	 * in other blogs for
	 * the selected element
	 * with additional informations
	 *
	 * @since   0.1
	 * @static
	 * @access  public
	 * @uses	get_current_blog_id, get_results, get_results
	 * @global	$wpdb WordPress Database Wrapper
	 * @return  array $elements
	 */
	public function get_interlinked_permalinks( $element_id = 0 ) {
		global $wpdb;

		// if no element id is provides, use WP default
		if ( 0 == $element_id )
			$element_id = get_the_ID();

		$blog_id = get_current_blog_id();

		// Get linked elements
		$results = $wpdb->get_results( $wpdb->prepare( 'SELECT t.ml_blogid, t.ml_elementid FROM ' . Multilingual_Press::$class_object->link_table . ' s INNER JOIN ' . Multilingual_Press::$class_object->link_table . ' t ON s.ml_source_blogid = t.ml_source_blogid && s.ml_source_elementid = t.ml_source_elementid WHERE s.ml_blogid = %d && s.ml_elementid = %d', $blog_id, $element_id ) );

		// No linked elements? Adios.
		if ( 0 >= count( $results ) )
			return array();

		// Walk results
		$elements = array();

		foreach ( $results as $resultelement ) {
			if ( $blog_id != $resultelement->ml_blogid ) {

				switch_to_blog( $resultelement->ml_blogid );
				$elements[ $resultelement->ml_blogid ] = array(
					'post_id'		=> ( int ) $resultelement->ml_elementid,
					'post_title'	=> get_the_title( $resultelement->ml_elementid ),
					'permalink'		=> get_permalink( $resultelement->ml_elementid ),
					'flag'			=> self::get_language_flag( $resultelement->ml_blogid ),
					'lang'			=> self::get_blog_language( $resultelement->ml_blogid )
				);
				restore_current_blog();
			}
		}

		return $elements;
	}

	/**
	 * function for custom plugins to get activated on all language blogs
	 *
	 * @since   0.1
	 * @access  public
	 * @param   int $element_id ID of the selected element
	 * @param   string $type type of the selected element
	 * @param   int $blog_id ID of the selected blog
	 * @return  array linked elements
	 */
	static public function run_custom_plugin( $element_id, $type, $blog_id, $hook, $param ) {

		if ( empty( $element_id ) ) {
			$error = new WP_Error( 'mlp_empty_custom_element', __( 'Empty Element', 'multilingualpress' ) );
			return $error;
		}

		if ( empty( $type ) ) {
			$error = new WP_Error( 'mlp_empty_custom_type', __( 'Empty Type', 'multilingualpress' ) );
			return $error;
		}

		if ( empty( $hook ) || !is_callable( $hook ) ) {
			$error = new WP_Error( 'mlp_empty_custom_hook', __( 'Invalid Hook', 'multilingualpress' ) );
			return $error;
		}

		// If no ID is provided, get current blogs' ID
		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		// set the current element in the mlp class
		self::$class_object -> set_source_id( $element_id, $blog_id, $type );
		$languages		= mlp_get_available_languages();
		$current_blog	= get_current_blog_id();

		if ( 0 < count( $languages ) ) {

			foreach ( $languages as $languageid => $languagename ) {
				if ( $current_blog != $languageid ) {
					switch_to_blog( $languageid );
					// custom hook
					$return = do_action( $hook, $param );
					restore_current_blog();
				}
			}
		}
	}

	/**
	 * Get the url of the
	 * flag from a blogid
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	get_current_blog_id, get_blog_option, get_site_option
	 * 			plugin_dir_path
	 * @param	int $blog_id ID of a blog
	 * @return	string url of the language image
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
		$language_code = ( 5 == strlen( $languages[ $blog_id ][ 'lang' ] ) ) ? strtolower( substr( $languages[ $blog_id ][ 'lang' ], 3, 2 ) ) : substr( $languages[ $blog_id ][ 'lang' ], 0, 2 );

		// Check for existing file
		if ( '' != $language_code && file_exists( plugin_dir_path( dirname( __FILE__ ) ) . '/flags/' . $language_code . '.gif' ) )
			$url = plugins_url( 'flags/' . $language_code . '.gif', dirname( __FILE__ ) );

		return $url;
	}

	/**
	 * Get the url of the
	 * flag from a blogid
	 *
	 * @since	0.7
	 * @access	public
	 * @uses	get_current_blog_id, get_site_option
	 * @param	int $blog_id ID of a blog
	 * @return	string Second part of language identifier
	 */
	static public function get_blog_language( $blog_id = 0 ) {

		if ( 0 == $blog_id )
			$blog_id = get_current_blog_id();

		// Get blog language code, which will make
		// part of the flags' file name, ie. "de.gif"
		$languages = get_site_option( 'inpsyde_multilingual' );

		// Is this a shortcode (i.e. "fr"), or an ISO
		// formatted language code (i.e. fr_BE) ?
		$language_code = ( 5 == strlen( $languages[ $blog_id ][ 'lang' ] ) ) ? strtolower( substr( $languages[ $blog_id ][ 'lang' ], 3, 2 ) ) : substr( $languages[ $blog_id ][ 'lang' ], 0, 2 );

		return $language_code;
	}

	/**
	 * Get the linked elements and display them as a list
	 * flag from a blogid
	 *
	 * @since	0.1
	 * @access	public
	 * @param	int $blog_id ID of a blog
	 * @uses	mlp_get_available_languages, mlp_get_available_languages_titles, is_single,
	 * 			is_page, mlp_get_linked_elements, mlp_get_language_flag, get_current_blog_id,
	 * 			get_blog_post, get_site_url
	 * @return	string output of the bloglist
	 */
	static public function show_linked_elements( $args ) {

		$output				= '';
		$languages			= mlp_get_available_languages();
		$language_titles	= mlp_get_available_languages_titles();

		if ( ! ( 0 < count( $languages ) ) )
			return $output;

		global $wp_query;

		$current_element_id = ( get_the_ID() == NULL ) ? $wp_query->queried_object->ID : get_the_ID();

		$linked_elements = array();
		if ( is_single() || is_page() )
			$linked_elements = mlp_get_linked_elements( $current_element_id );

		$defaults = array(
			'link_text' => 'text', 'echo' => TRUE,
			'sort' => 'blogid', 'show_current_blog' => FALSE,
		);

		$params = wp_parse_args( $args, $defaults );

		if ( 'blogid' == $params[ 'sort' ] )
			ksort( $languages );
		else
			asort( $languages );

		$output .= '<div class="mlp_language_box"><ul>';

		foreach ( $languages as $language_blog => $language_string ) {

			$current_language = mlp_get_current_blog_language( 2 );
			if ( $current_language == $language_string && $params[ 'show_current_blog' ] == FALSE )
				continue;

			// Get params
			$flag = mlp_get_language_flag( $language_blog );
			$title = mlp_get_available_languages_titles( TRUE );

			// Display type
			if ( 'flag' == $params[ 'link_text' ] && '' != $flag )
				$display = '<img src="' . $flag . '" alt="' . $languages[ $language_blog ] . '" title="' . $title[ $language_blog ] . '" />';
			else if ( 'text' == $params[ 'link_text' ] && ! empty( $language_titles[ $language_blog ] ) )
				$display = $language_titles[ $language_blog ];
			else if ( 'text_flag' == $params[ 'link_text' ] ) {
				$display  = '<img src="' . $flag . '" alt="' . $languages[ $language_blog ] . '" title="' . $title[ $language_blog ] . '" />';
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

			do_action( 'mlp_before_link' );
			$link =
				( is_single() || is_page() || is_home() ) &&
				isset( $post->post_status ) &&
				( 'publish' === $post->post_status || ( 'private' === $post->post_status && is_super_admin() ) )
				?
				// get element link if available
				get_blog_permalink( $language_blog, $linked_elements[ $language_blog ] )
				:
				// link to siteurl of blog
				get_site_url( $language_blog );

			// apply filter to help others to change the link
			$link = apply_filters( 'mlp_linked_element_link', $link, $language_blog, $linked_elements[ $language_blog ] );
			do_action( 'mlp_after_link' );

			// Output link elements
			$output .= '<li ' . ( $current_language == $language_string ? 'class="current"' : '' ) . '><a rel="alternate" hreflang="' . self::get_blog_language( $language_blog ).'" ' . $class . ' href="' . $link . '">' . $display . '</a></li>';
		}
		$output .= '</ul></div>';
		return $output;
	}
} // end class

/**
 * Wrapper for Mlp_Helpers:is_redirect, which returns
 * a blog's redirect setting
 *
 * @since	0.5.2a
 * @param	int $blogid
 * @return	bool TRUE/FALSE
 */
function mlp_is_redirect( $blogid = FALSE ) {
	return Mlp_Helpers::is_redirect( $blogid );
}

/**
 * wrapper of Mlp_Helpers:get_current_blog_language
 * return current blog's language code ( not the locale used by WordPress,
 * but the one set by MlP)
 *
 * @since	0.1
 * @return	array Available languages
 */
function mlp_get_current_blog_language( $count ) {
	return Mlp_Helpers::get_current_blog_language( $count );
}

/**
 * wrapper of Mlp_Helpers:get_available_languages
 * load the available languages
 *
 * @since	0.1
 * @return	array Available languages
 */
function mlp_get_available_languages( $nonrelated = FALSE ) {
	return Mlp_Helpers::get_available_languages( $nonrelated );
}

/**
 * wrapper of Mlp_Helpers:: get_available_language_title
 * load the available language titles
 *
 * @since	0.5.3b
 * @return	array Available languages
 */
function mlp_get_available_languages_titles( $nonrelated = FALSE ) {
	return Mlp_Helpers::get_available_languages_titles( $nonrelated );
}

/**
 * wrapper of Mlp_Helpers function to get the element ID in other blogs for the selected element
 *
 * @since	0.1
 * @param	int $element_id ID of the selected element
 * @param	string $type type of the selected element
 * @param	int $blog_id ID of the selected blog
 * @return	array linked elements
 */
function mlp_get_linked_elements( $element_id = FALSE, $type = '', $blog_id = 0 ) {
	return Mlp_Helpers::load_linked_elements( $element_id, $type, $blog_id );
}

/**
 * wrapper of Mlp_Helpers function for custom plugins to get activated on all language blogs
 *
 * @since	0.1
 * @param	int $element_id ID of the selected element
 * @param	string $type type of the selected element
 * @param	int $blog_id ID of the selected blog
 * @param	string $hook name of the hook that will be executed
 * @param	array $param parameters for the function
 * @return	array linked elements
 */
function mlp_run_custom_plugin( $element_id = FALSE, $type = '', $blog_id = 0, $hook = NULL, $param = NULL ) {
	return Mlp_Helpers::run_custom_plugin( $element_id, $type, $blog_id, $hook, $param );
}

/**
 * wrapper of Mlp_Helpers function for function to get the url of the flag from a blogid

 * @since	0.1
 * @param	int $blog_id ID of a blog
 * @return	string url of the language image
 */
function mlp_get_language_flag( $blog_id = 0 ) {
	return Mlp_Helpers::get_language_flag( $blog_id );
}

/**
 * wrapper of Mlp_Helpers function for function to get the linked elements and display them as a list
 *
 * @since	0.8
 * @param	string $link_type available types: flag, text, text_flag
 * @param	bool $echo to display the output or to return. default is display
 * @return	string output of the bloglist
 */
function mlp_show_linked_elements( $args_or_deprecated_text = 'text', $deprecated_echo = TRUE, $deprecated_sort = 'blogid' ) {

	$args = is_array( $args_or_deprecated_text ) ?
		$args_or_deprecated_text
		:
		array(
			'link_text' => $args_or_deprecated_text,
			'echo' => $deprecated_echo,
			'sort' => $deprecated_sort,
		);

	$defaults = array(
		'link_text' => 'text', 'echo' => TRUE,
		'sort' => 'blogid', 'show_current_blog' => FALSE,
	);

	$params = wp_parse_args( $args, $defaults );

	$output = Mlp_Helpers::show_linked_elements( $params );

	if ( TRUE === $params[ 'echo' ] )
		echo $output;
	else
		return $output;
}

/**
 * get the linked elements with a lot of more information
 *
 * @since	0.7
 * @param	int $element_id current post / page / whatever
 * @return	array
 */
function mlp_get_interlinked_permalinks( $element_id = 0 ) {
	return Mlp_Helpers::get_interlinked_permalinks( $element_id );
}

/**
 * get the blog language
 *
 * @since	0.7
 * @return	string Second part of language identifier
 */
function get_blog_language( $blog_id = 0 ) {
	return Mlp_Helpers::get_blog_language( $blog_id );
}

/**
 * Get language representation.
 *
 * @since 1.0.4
 * @param string $iso Two-letter code like "en" or "de"
 * @param string $field Sub-key name: "iso_639_2", "en" or "native",
 *               defaults to "native", "all" returns the complete list.
 * @return boolean|array|string FALSE for unknown language codes or fields,
 *               array for $field = 'all' and string for specific fields
 */
function mlp_get_lang_by_iso( $iso, $field = 'native' ) {
	return Mlp_Helpers::mlp_get_lang_by_iso( $iso, $field );
}
