<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress {

/**
 * Returns the according HTML string representation for the given array of attributes.
 *
 * @since 3.0.0
 *
 * @param string[] $attributes An array of HTML attribute names as keys and the according values.
 *
 * @return string The according HTML string representation for the given array of attributes.
 */
function attributes_array_to_string( array $attributes ) {

	if ( ! $attributes ) {
		return '';
	}

	$strings = [];

	array_walk( $attributes, function ( $value, $name ) use ( &$strings ) {

		$strings[] = $name . '="' . esc_attr( true === $value ? $name : $value ) . '"';
	} );

	return implode( ' ', $strings );
}

/**
 * Writes debug data to the error log.
 *
 * To enable this function, add the following line to your wp-config.php file:
 *
 *     define( 'MULTILINGUALPRESS_DEBUG', true );
 *
 * @since 3.0.0
 *
 * @param string $message The message to be logged.
 *
 * @return void
 */
function debug( $message ) {

	if ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG ) {
		error_log( sprintf(
			'MultilingualPress: %s %s',
			date( 'H:m:s' ),
			$message
		) );
	}
}

/**
 * Returns the individual MultilingualPress language code of all (related) sites.
 *
 * @since 3.0.0
 *
 * @param bool $related_sites_only Optional. Restrict to related sites only? Defaults to true.
 *
 * @return string[] An array with site IDs as keys and the individual MultilingualPress language code as values.
 */
function get_available_languages( $related_sites_only = true ) {

	// TODO: Do not hard-code the option name, and maybe even get the languages some other way.
	$languages = (array) get_network_option( null, 'inpsyde_multilingual', [] );
	if ( ! $languages ) {
		return [];
	}

	if ( $related_sites_only ) {
		$related_site_ids = MultilingualPress::resolve( 'multilingualpress.site_relations' )->get_related_site_ids();
		if ( ! $related_site_ids ) {
			return [];
		}

		// Restrict ro related sites.
		$languages = array_diff_key( $languages, array_flip( $related_site_ids ) );
	}

	$available_languages = [];

	// TODO: In the old option, there might also be sites with a "-1" as lang value. Update the option, and set to "".
	array_walk( $languages, function ( $language_data, $site_id ) use ( &$available_languages ) {

		if ( isset( $language_data['lang'] ) ) {
			$available_languages[ (int) $site_id ] = (string) $language_data['lang'];
		}
	} );

	return $available_languages;
}

/**
 * Returns the MultilingualPress language for the current site.
 *
 * @since 3.0.0
 *
 * @param bool $language_only Optional. Whether or not to return the language part only. Defaults to false.
 *
 * @return string The MultilingualPress language for the current site.
 */
function get_current_site_language( $language_only = false ) {

	return get_site_language( get_current_blog_id(), $language_only );
}

/**
 * Returns the given content ID, if valid, and the ID of the queried object otherwise.
 *
 * @since 3.0.0
 *
 * @param int $content_id Content ID.
 *
 * @return int The given content ID, if valid, and the ID of the queried object otherwise.
 */
function get_default_content_id( $content_id ) {

	return (int) ( ( 0 < $content_id ) ? $content_id : get_queried_object_id() );
}

/**
 * Returns the MultilingualPress language for the site with the given ID.
 *
 * @since 3.0.0
 *
 * @param int  $site_id       Optional. Site ID. Defaults to 0.
 * @param bool $language_only Optional. Whether or not to return the language part only. Defaults to false.
 *
 * @return string The MultilingualPress language for the site with the given ID.
 */
function get_site_language( $site_id = 0, $language_only = false ) {

	$site_id = $site_id ?: get_current_blog_id();

	// TODO: Don't hardcode the option name.
	$languages = get_network_option( null, 'inpsyde_multilingual' );

	// TODO: Maybe also don't hardcode the 'lang' key...?
	if ( ! isset( $languages[ $site_id ]['lang'] ) ) {
		return '';
	}

	return $language_only
		? strtok( $languages[ $site_id ]['lang'], '_' )
		: (string) $languages[ $site_id ]['lang'];
}

/**
 * Checks if the site with the given ID has HTTP redirection enabled.
 *
 * If no ID is passed, the current site is checked.
 *
 * @since 3.0.0
 *
 * @param int $site_id Optional. Site ID. Defaults to 0.
 *
 * @return bool Whether or not the site with the given ID has HTTP redirection enabled.
 */
function is_redirect_enabled( $site_id = 0 ) {

	// TODO: Don't hard-code the option name.
	return (bool) get_blog_option( $site_id ?: get_current_blog_id(), 'inpsyde_multilingual_redirect' );
}

}



// TODO: Move all functions to Inpsyde\MultilingualPress namespace (see below) and adapt names (no prefix etc.).
namespace {

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

		exit( $status );
	}

	/**
	 * Checks if MultilingualPress debug mode is on.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not MultilingualPress debug mode is on.
	 */
	function mlp_is_debug_mode() {

		return ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG );
	}

	/**
	 * Checks if either MultilingualPress or WordPress debug mode is on.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not MultilingualPress or WordPress debug mode is on.
	 */
	function mlp_is_wp_debug_mode() {

		return mlp_is_debug_mode() || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	}

	/**
	 * Checks if either MultilingualPress or script debug mode is on.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not MultilingualPress or script debug mode is on.
	 */
	function mlp_is_script_debug_mode() {

		return mlp_is_debug_mode() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	/**
	 * wrapper of Mlp_Helpers:: get_available_language_title
	 * load the available language titles
	 *
	 * @since    0.5.3b
	 *
	 * @param  bool $related
	 *
	 * @return    array Available languages
	 */
	function mlp_get_available_languages_titles( $related = true ) {

		return Mlp_Helpers::get_available_languages_titles( $related );
	}

	/**
	 * wrapper of Mlp_Helpers function to get the element ID in other blogs for the selected element
	 *
	 * @since    0.1
	 *
	 * @param    int    $element_id ID of the selected element
	 * @param    string $type       type of the selected element
	 * @param    int    $blog_id    ID of the selected blog
	 *
	 * @return    array linked elements
	 */
	function mlp_get_linked_elements( $element_id = 0, $type = '', $blog_id = 0 ) {

		return Mlp_Helpers::load_linked_elements( $element_id, $type, $blog_id );
	}

	/**
	 * wrapper of Mlp_Helpers function for function to get the url of the flag from a blogid
	 *
	 * @since    0.1
	 *
	 * @param    int $blog_id ID of a blog
	 *
	 * @return    string url of the language image
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
	 * @param bool         $deprecated_echo         Optional. Display the output? Defaults to TRUE.
	 * @param string       $deprecated_sort         Optional. Sort elements. Defaults to 'blogid'.
	 *
	 * @return string
	 */
	function mlp_show_linked_elements( $args_or_deprecated_text = 'text', $deprecated_echo = true, $deprecated_sort = 'blogid' ) {

		$args     = is_array( $args_or_deprecated_text )
			? $args_or_deprecated_text
			: [
				'link_text' => $args_or_deprecated_text,
				'sort'      => $deprecated_sort,
			];
		$defaults = [
			'link_text'         => 'text',
			'sort'              => 'priority',
			'show_current_blog' => false,
			'display_flag'      => false,
			'strict'            => false, // get exact translations only
		];
		$params   = wp_parse_args( $args, $defaults );
		$output   = Mlp_Helpers::show_linked_elements( $params );

		$echo = isset( $params['echo'] ) ? $params['echo'] : $deprecated_echo;
		if ( $echo ) {
			echo $output;
		}

		return $output;
	}

	/**
	 * get the linked elements with a lot of more information
	 *
	 * @since    0.7
	 *
	 * @param    int $element_id current post / page / whatever
	 *
	 * @return    array
	 */
	function mlp_get_interlinked_permalinks( $element_id = 0 ) {

		return Mlp_Helpers::get_interlinked_permalinks( $element_id );
	}

	/**
	 * Get language representation.
	 *
	 * @since 1.0.4
	 *
	 * @param string $iso   Two-letter code like "en" or "de"
	 * @param string $field Sub-key name: "iso_639_2", "en" or "native",
	 *                      defaults to "native", "all" returns the complete list.
	 *
	 * @return boolean|array|string FALSE for unknown language codes or fields,
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
		 *
		 * @param  int $blog_id
		 * @param  int $site_id
		 *
		 * @return bool
		 */
		function blog_exists( $blog_id, $site_id = 0 ) {

			/** @type wpdb $wpdb */
			global $wpdb;
			static $cache = [];

			$site_id = (int) $site_id;

			if ( 0 === $site_id ) {
				$site_id = get_current_site()->id;
			}

			if ( empty ( $cache ) or empty ( $cache[ $site_id ] ) ) {

				if ( wp_is_large_network() ) // we do not test large sites.
				{
					return true;
				}

				$query = "SELECT `blog_id` FROM $wpdb->blogs
					WHERE site_id = $site_id AND deleted = 0";

				$result = $wpdb->get_col( $query );

				// Make sure the array is always filled with something.
				if ( empty ( $result ) ) {
					$cache[ $site_id ] = [ 'do not check again' ];
				} else {
					$cache[ $site_id ] = $result;
				}
			}

			return in_array( $blog_id, $cache[ $site_id ] );
		}
	}
}
