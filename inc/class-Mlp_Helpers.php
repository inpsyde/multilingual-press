<?php
/**
 * Module Name:	Multilingual Press Helpers
 * Description:	Several helper functions
 * Author:		Inpsyde GmbH
 * Version:		0.6
 * Author URI:	http://inpsyde.com
 *
 * Changelog
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
	 * @param   $rel | filter out non-related blogs? By default
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
				break;
			
			// Filter out blogs that are not related
			if ( isset( $related_blogs ) && is_array( $related_blogs ) && ! in_array( $language_blogid, $related_blogs ) )
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
	 * @return  array $options
	 */
	static public function get_available_languages_titles( $nonrelated = FALSE ) {
		
		$related_blogs = '';

		$languages = get_site_option( 'inpsyde_multilingual' );

		if ( FALSE === $nonrelated )
			$related_blogs = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_blog_relationship' );

		if ( ! is_array( $related_blogs ) && FALSE === $nonrelated )
			return;

		$options = array( );
		
		foreach ( $languages as $language_blogid => $language_data ) {

			// Filter out blogs that are not related
			if ( is_array( $related_blogs ) && ! in_array( $language_blogid, $related_blogs ) && FALSE === $nonrelated )
				continue;

			$lang = $language_data[ 'text' ];
			
			// I didn't write this block :/
			if ( '' == $lang ) {
				$lang = substr( $language_data[ 'lang' ], 0, 2 ); // get the first lang element
				if ( is_admin() ) {
					$lang = format_code_lang( $lang );
				}
			}
			$options[ $language_blogid ] = $lang;
		}
		return $options;
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
		$results = $wpdb->get_results( $wpdb->prepare( 'SELECT t.ml_blogid, t.ml_elementid FROM ' . Multilingual_Press::$class_object->link_table . ' s INNER JOIN ' . Multilingual_Press::$class_object->link_table . ' t ON s.ml_source_blogid = t.ml_source_blogid && s.ml_source_elementid = t.ml_source_elementid WHERE s.ml_blogid = %d && s.ml_elementid = %d', $blog_id, $element_id ) );
		
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
			$error = new WP_Error( 'mlp_empty_custom_element', __( 'Empty Element', self::get_textdomain() ) );
			return $error;
		}

		if ( empty( $type ) ) {
			$error = new WP_Error( 'mlp_empty_custom_type', __( 'Empty Type', self::get_textdomain() ) );
			return $error;
		}
		
		if ( empty( $hook ) || !is_callable( $hook ) ) {
			$error = new WP_Error( 'mlp_empty_custom_hook', __( 'Invalid Hook', self::get_textdomain() ) );
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
	static public function show_linked_elements( $link_type ) {

		$output				= '';
		$languages			= mlp_get_available_languages( TRUE );
		$language_titles	= mlp_get_available_languages_titles();
			
		if ( ! ( 0 < count( $languages ) ) )
			return $output;

		if ( is_single() || is_page() )
			$linked_elements = mlp_get_linked_elements( get_the_id() );

		$output .= '<div class="mlp_language_box"><ul>';

		foreach ( $languages as $language_blog => $language_string ) {
			
			$current_language = mlp_get_current_blog_language( 2 );
			if ( $current_language == $language_string )
				continue;

			// Get params
			$flag = mlp_get_language_flag( $language_blog );
			$title = mlp_get_available_languages_titles( TRUE );

			// Display type
			if ( 'flag' == $link_type && '' != $flag )
				$display = '<img src="' . $flag . '" alt="' . $languages[ $language_blog ] . '" title="' . $title[ $language_blog ] . '" />';
			else if ( 'text' == $link_type && ! empty( $language_titles[ $language_blog ] ) )
				$display = $language_titles[ $language_blog ];
			else if ( 'text_flag' == $link_type ) {
				$display  = '<img src="' . $flag . '" alt="' . $languages[ $language_blog ] . '" title="' . $title[ $language_blog ] . '" />';
				if ( ! empty( $language_titles[ $language_blog ] ) )
					$display .= ' ' . $language_titles[ $language_blog ];
			}
			else
				$display = $languages[ $language_blog ];

			$class = ( get_current_blog_id() == $language_blog ) ? 'id="mlp_current_locale"' : '';

			// Check post status
			$post = ( isset( $linked_elements[ $language_blog ] ) ) ? get_blog_post( $language_blog, $linked_elements[ $language_blog ] ) : '';
			
			// Output link elements
			$output .= '<li><a ' . $class . ' href="' . ( ( is_single() || is_page() ) && isset( $linked_elements[ $language_blog ] ) && 'publish' === $post->post_status ? get_blog_permalink( $language_blog, $linked_elements[ $language_blog ] ) : get_site_url( $language_blog ) ) . '?noredirect=' . $language_string . '">' . $display . '</a></li>';
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
 * @access	public
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
 * @access	public
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
 * @access	public
 * @since	0.5.3b
 * @return	array Available languages
 */
function mlp_get_available_languages_titles( $nonrelated = FALSE ) {
	return Mlp_Helpers::get_available_languages_titles( $nonrelated );
}

/**
 * wrapper of Mlp_Helpers function to get the element ID in other blogs for the selected element  
 *
 * @access	public
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
 * @access	public
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

 * @access	public
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
* @access	public
* @since	0.8
* @param	string $link_type available types: flag, text, text_flag
* @param	bool $echo to display the output or to return. default is display
* @return	string output of the bloglist
*/
function mlp_show_linked_elements( $link_type = 'text', $echo = TRUE ) {
	$output = Mlp_Helpers::show_linked_elements( $link_type );
	if ( TRUE === $echo )
		echo $output;
	else
		return $output;
}