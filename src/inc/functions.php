<?php # -*- coding: utf-8 -*-

/**
 * Wrapper for the exit language construct.
 *
 * Introduced to allow for easy unit testing.
 *
 * @param int|string $status Exit status.
 *
 * @return void
 */
function mlp_exit( $status = '' ) {

	exit( esc_html( $status ) );
}

/**
 * Wrapper for Mlp_Helpers:is_redirect, which returns
 * a blog's redirect setting
 *
 * @since   0.5.2a
 * @param   bool $blogid
 * @return  bool true/false
 */
function mlp_is_redirect( $blogid = false ) {
	return Mlp_Helpers::is_redirect( $blogid );
}

/**
 * wrapper of Mlp_Helpers:get_current_blog_language
 * return current blog's language code ( not the locale used by WordPress,
 * but the one set by MlP)
 *
 * @since   0.1
 * @param   bool $short
 * @return  array Available languages
 */
function mlp_get_current_blog_language( $short = false ) {
	return Mlp_Helpers::get_current_blog_language( $short );
}

/**
 * wrapper of Mlp_Helpers:get_available_languages
 * load the available languages
 *
 * @since   0.1
 * @param  bool $nonrelated
 * @return  array Available languages
 */
function mlp_get_available_languages( $nonrelated = false ) {
	return Mlp_Helpers::get_available_languages( $nonrelated );
}

/**
 * wrapper of Mlp_Helpers:: get_available_language_title
 * load the available language titles
 *
 * @since   0.5.3b
 * @param  bool $related
 * @return  array Available languages
 */
function mlp_get_available_languages_titles( $related = true ) {
	return Mlp_Helpers::get_available_languages_titles( $related );
}

/**
 * wrapper of Mlp_Helpers function to get the element ID in other blogs for the selected element
 *
 * @since   0.1
 * @param   int $element_id ID of the selected element
 * @param   string $type type of the selected element
 * @param   int $blog_id ID of the selected blog
 * @return  array linked elements
 */
function mlp_get_linked_elements( $element_id = 0, $type = '', $blog_id = 0 ) {
	return Mlp_Helpers::load_linked_elements( $element_id, $type, $blog_id );
}

/**
 * wrapper of Mlp_Helpers function for custom plugins to get activated on all language blogs
 *
 * @since   0.1
 * @param   int $element_id ID of the selected element
 * @param   string $type type of the selected element
 * @param   int $blog_id ID of the selected blog
 * @param   string $hook name of the hook that will be executed
 * @param   array $param parameters for the function
 * @return  WP_Error|null
 */
function mlp_run_custom_plugin( $element_id = 0, $type = '', $blog_id = 0, $hook = null, $param = null ) {
	return Mlp_Helpers::run_custom_plugin( $element_id, $type, $blog_id, $hook, $param );
}

/**
 * wrapper of Mlp_Helpers function for function to get the url of the flag from a blogid

 * @since   0.1
 * @param   int $blog_id ID of a blog
 * @return  string url of the language image
 */
function mlp_get_language_flag( $blog_id = 0 ) {
	return Mlp_Helpers::get_language_flag( $blog_id );
}

/**
 * Wrapper for Mlp_Helpers::show_linked_elements().
 *
 * @see Mlp_Helpers::show_linked_elements()
 *
 * @param array|string $args_or_deprecated_text Arguments array, or value for the 'link_text' argument.
 * @param bool         $deprecated_echo         Optional. Display the output? Defaults to true.
 * @param string       $deprecated_sort         Optional. Sort elements. Defaults to 'blogid'.
 *
 * @return string
 */
function mlp_show_linked_elements( $args_or_deprecated_text = 'text', $deprecated_echo = true, $deprecated_sort = 'blogid' ) {

	$args = is_array( $args_or_deprecated_text )
		? $args_or_deprecated_text
		: array(
			'link_text' => $args_or_deprecated_text,
			'sort'      => $deprecated_sort,
		);
	$defaults = array(
		'link_text'         => 'text',
		'sort'              => 'priority',
		'show_current_blog' => false,
		'display_flag'      => false,
		'strict'            => false, // get exact translations only
	);
	$params = wp_parse_args( $args, $defaults );
	$output = Mlp_Helpers::show_linked_elements( $params );

	$echo = isset( $params['echo'] ) ? $params['echo'] : $deprecated_echo;
	if ( $echo ) {
		echo wp_kses_post( $output );
	}

	return $output;
}

/**
 * get the linked elements with a lot of more information
 *
 * @since   0.7
 * @param   int $element_id current post / page / whatever
 * @return  array
 */
function mlp_get_interlinked_permalinks( $element_id = 0 ) {
	return Mlp_Helpers::get_interlinked_permalinks( $element_id );
}

/**
 * Return the language for the given blog.
 *
 * @param int  $blog_id Blog ID.
 * @param bool $short   Return only the first part of the language code?
 *
 * @return string
 */
function mlp_get_blog_language( $blog_id = 0, $short = true ) {

	return Mlp_Helpers::get_blog_language( $blog_id, $short );
}

// TODO: Eventually remove this, with version 2.2.0 + 4 at the earliest.
if ( ! function_exists( 'get_blog_language' ) ) {

	/**
	 * Deprecated! Return the language for the given blog.
	 *
	 * @param int  $blog_id Blog ID.
	 * @param bool $short   Return only the first part of the language code?
	 *
	 * @return string
	 */
	function get_blog_language( $blog_id = 0, $short = true ) {

		_deprecated_function( __FUNCTION__, '2.2.0', 'mlp_get_blog_language' );

		return Mlp_Helpers::get_blog_language( $blog_id, $short );
	}
}

/**
 * Get language representation.
 *
 * @since 1.0.4
 * @param string $iso Two-letter code like "en" or "de"
 * @param string $field Sub-key name: "iso_639_2", "en" or "native",
 *               defaults to "native", "all" returns the complete list.
 * @return boolean|array|string false for unknown language codes or fields,
 *               array for $field = 'all' and string for specific fields
 */
function mlp_get_lang_by_iso( $iso, $field = 'native_name' ) {
	return Mlp_Helpers::get_lang_by_iso( $iso, $field );
}


if ( ! function_exists( 'blog_exists' ) ) {

	/**
	 * Checks if a blog exists and is not marked as deleted.
	 *
	 * @link   http://wordpress.stackexchange.com/q/138300/73
	 * @param  int $blog_id
	 * @param  int $site_id
	 * @return bool
	 */
	function blog_exists( $blog_id, $site_id = 0 ) {

		/** @type wpdb $wpdb */
		global $wpdb;
		static $cache = array();

		$site_id = (int) $site_id;

		if ( 0 === $site_id ) {
			$site_id = get_current_site()->id;
		}

		if ( empty( $cache ) || empty( $cache[ $site_id ] ) ) {

			if ( wp_is_large_network() ) { // we do not test large sites.
				return true;
			}

			$result = $wpdb->get_col( $wpdb->prepare(
				"SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d AND deleted = 0",
				$site_id
			) );

			// Make sure the array is always filled with something.
			if ( empty( $result ) ) {
				$cache[ $site_id ] = array( 'do not check again' );
			} else {
				$cache[ $site_id ] = array_map( 'intval', $result );
			}
		}

		return in_array( (int) $blog_id, $cache[ $site_id ], true );
	}
}
