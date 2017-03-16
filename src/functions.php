<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Common\Locations;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Common\Type\URL;

/**
 * Adds a "Settings saved." message for the given setting.
 *
 * @since 3.0.0
 *
 * @param string $setting Optional. Setting slug. Defaults to 'mlp-setting'.
 * @param string $code    Optional. Setting code for identification. Defaults to 'mlp-setting'.
 *
 * @return void
 */
function add_settings_updated_message( $setting = 'mlp-setting', $code = 'mlp-setting' ) {

	$messages = get_transient( 'settings_errors' );
	if ( ! is_array( $messages ) ) {
		$messages = [];
	}

	$messages[ $code ] = [
		'setting' => $setting,
		'code'    => $code,
		'message' => __( 'Settings saved.', 'multilingual-press' ),
		'type'    => 'updated',
	];

	set_transient( 'settings_errors', $messages );
}

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
 * Wrapper for the exit language construct.
 *
 * Introduced to allow for easy unit testing.
 *
 * @since 3.0.0
 *
 * @param int|string $status Exit status.
 *
 * @return void
 */
function call_exit( $status = '' ) {

	exit( $status );
}

/**
 * Checks if the given nonce is valid, and if not, terminates WordPress execution unless this is an admin request.
 *
 * This function is the MultilingualPress equivalent of the WordPress function with the same name.
 *
 * @since 3.0.0
 *
 * @param Nonce $nonce Nonce object.
 *
 * @return bool Whether or not the nonce is valid.
 */
function check_admin_referer( Nonce $nonce ) {

	if ( $nonce->is_valid() ) {
		return true;
	}

	if ( 0 !== strpos( strtolower( wp_get_referer() ), strtolower( admin_url() ) ) ) {
		wp_nonce_ays( null );
		call_exit();
	}

	return false;
}

/**
 * Checks if the given nonce is valid, and if not, terminates WordPress execution according to passed flag.
 *
 * This function is the MultilingualPress equivalent of the WordPress function with the same name.
 *
 * @since 3.0.0
 *
 * @param Nonce $nonce     Nonce object.
 * @param bool  $terminate Optional. Terminate WordPress execution in case the nonce is invalid? Defaults to true.
 *
 * @return bool Whether or not the nonce is valid.
 */
function check_ajax_referer( Nonce $nonce, $terminate = true ) {

	$is_nonce_valid = $nonce->is_valid();

	if ( $terminate && ! $is_nonce_valid ) {
		if ( wp_doing_ajax() ) {
			wp_die( '-1' );
		} else {
			call_exit( '-1' );
		}
	}

	return $is_nonce_valid;
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
 * Returns the names of all available languages according to the given arguments.
 *
 * @since 3.0.0
 *
 * @param bool $related              Optional. Include related sites of the current site only? Defaults to true.
 * @param bool $include_current_site Optional. Include the current site? Defaults to true.
 *
 * @return string[] The names of all available languages.
 */
function get_available_language_names( $related = true, $include_current_site = true ) {

	$current_site_id = (int) get_current_blog_id();

	$related_sites = [];

	if ( $related ) {
		$related_sites = MultilingualPress::resolve( 'multilingualpress.site_relations' )->get_related_site_ids(
			$current_site_id,
			$include_current_site
		);
		if ( ! $related_sites ) {
			return [];
		}
	}

	$language_settings = MultilingualPress::resolve( 'multilingualpress.site_settings_repository' )->get_settings();
	if ( ! $language_settings ) {
		return [];
	}

	if ( ! $include_current_site ) {
		unset( $language_settings[ $current_site_id ] );
	}

	$languages = [];

	foreach ( $language_settings as $site_id => $language_data ) {
		if ( $related_sites && ! in_array( $site_id, $related_sites ) ) {
			continue;
		}

		$value = $language_data['text'] ?? '';

		if ( ! $value && isset( $language_data['lang'] ) ) {
			$value = get_language_by_http_name( str_replace( '_', '-', $language_data['lang'] ) );
		}

		if ( $value ) {
			$languages[ $site_id ] = (string) $value;
		}
	}

	return $languages;
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

	$languages = MultilingualPress::resolve( 'multilingualpress.site_settings_repository' )->get_settings();
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

	return (int) $content_id ?: get_queried_object_id();
}

/**
 * Returns the URL of the flag image for the given (or current) site ID.
 *
 * @since 3.0.0
 *
 * @param int $site_id Optional. Site ID. Defaults to 0.
 *
 * @return URL Flag URL object.
 */
function get_flag_url_for_site( $site_id = 0 ) {

	$site_id = (int) $site_id ?: get_current_blog_id();

	$type_factory = MultilingualPress::resolve( 'multilingualpress.type_factory' );

	$url = MultilingualPress::resolve( 'multilingualpress.site_settings_repository' )->get_flag_image_url( $site_id );
	if ( $url ) {
		return $type_factory->create_url( [
			$url,
		] );
	}

	$internal_locations = MultilingualPress::resolve( 'multilingualpress.internal_locations' );

	$file_name = get_site_language( $site_id, true ) . '.gif';

	if ( is_readable( $internal_locations->get( 'flags', Locations::TYPE_PATH ) . "/$file_name" ) ) {
		return $type_factory->create_url( [
			$internal_locations->get( 'flags', Locations::TYPE_URL ) . $file_name,
		] );
	}

	return $type_factory->create_url( [
		'',
	] );
}

/**
 * Returns the desired field value of the language with the given HTTP code.
 *
 * @since 3.0.0
 *
 * @param string          $http_code Language HTTP code.
 * @param string          $field     Optional. The field which should be queried. Defaults to 'native_name'.
 * @param string|string[] $fallbacks Optional. Falback language fields. Defaults to native and English name.
 *
 * @return string|string[] The desired field value, an empty string on failure, or an array for field 'all'.
 */
function get_language_by_http_name(
	$http_code,
	$field = 'native_name',
	$fallbacks = [
		'native_name',
		'english_name',
	]
) {

	return MultilingualPress::resolve( 'multilingualpress.languages' )->get_language_by_http_code(
		$http_code,
		$field,
		$fallbacks
	);
}

/**
 * Renders a list of all translations according to the given arguments
 *
 * @since 3.0.0
 *
 * @param array $args Optional. Arguments array. Defaults to empty array.
 *
 * @return string The generated HTML.
 */
function get_linked_elements( array $args = [] ) {

	$args = wp_parse_args( $args, [
		'link_text'         => 'native',
		'display_flag'      => false,
		'sort'              => 'priority',
		'show_current_blog' => false,
		'strict'            => false,
	] );

	$output = '';

	$translations = MultilingualPress::resolve( 'multilingualpress.translations' )->get_translations( [
		'strict'       => $args['strict'],
		'include_base' => $args['show_current_blog'],
	] );
	if ( $translations ) {
		$translations = array_filter( $translations, function ( Translation $translation ) {

			return (bool) $translation->remote_url();
		} );

		$link_text = $args['link_text'];

		$translations = array_map( function ( Translation $translation ) use ( $link_text ) {

			$language = $translation->language();

			return [
				'url'      => $translation->remote_url(),
				'http'     => $language->name( 'http' ),
				'name'     => $language->name( $link_text ),
				'priority' => $language->priority(),
				'icon'     => (string) $translation->icon_url(),
			];
		}, $translations );

		switch ( $args['sort'] ) {
			case 'blogid':
				ksort( $translations );
				break;

			case 'priority':
				uasort( $translations, function ( array $a, array $b ) {

					return $b['priority'] <=> $a['priority'];
				} );
				break;

			case 'name':
				uasort( $translations, function ( array $a, array $b ) {

					return strcasecmp( $a['name'], $b['name'] );
				} );
				break;
		}

		$current_site_id = get_current_blog_id();

		$output = '<div class="mlp-language-box mlp_language_box"><ul>';

		foreach ( $translations as $site_id => $translation ) {
			$name = $translation['name'];

			$img = ( ! empty( $translation['icon'] ) && $args['display_flag'] )
				? '<img src="' . esc_url( $translation['icon'] ) . '" alt="' . esc_attr( $name ) . '"> '
				: '';

			if ( $current_site_id === $site_id ) {
				$output .= '<li><a class="mlp-current-language-item" href="">' . $img . esc_html( $name ) . '</a></li>';
			} else {
				$output .= sprintf(
					'<li><a rel="alternate" hreflang="%1$s" href="%2$s">%3$s%4$s</a></li>',
					esc_attr( $translation['http'] ),
					esc_url( $translation['url'] ),
					$img,
					esc_html( $name )
				);
			}
		}

		$output .= '</ul></div>';
	}

	/**
	 * Filters the output of the linked elements.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $output       The generated HTML.
	 * @param array[] $translations The translations.
	 * @param array   $args         The passed arguments (including missing defaults).
	 */
	$output = (string) apply_filters( 'multilingualpress.linked_elements_html', $output, $translations, $args );

	if ( ! empty( $args['echo'] ) ) {
		echo $output;
	}

	return $output;
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

	$lang = MultilingualPress::resolve( 'multilingualpress.site_settings_repository' )->get_site_language( $site_id );
	if ( ! $lang ) {
		return '';
	}

	if ( $language_only ) {
		return strtok( $lang, '_' );
	}

	return $lang;
}

/**
 * Returns the content IDs of all translations for the given content element data.
 *
 * @since 3.0.0
 *
 * @param int    $content_id Optional. Content ID. Defaults to 0.
 * @param string $type       Optional. Content type. Defaults to 'post'.
 * @param int    $site_id    Optional. Site ID. Defaults to 0.
 *
 * @return int[] An array with site IDs as keys and content IDs as values.
 */
function get_translation_ids( $content_id = 0, $type = 'post', $site_id = 0 ) {

	$content_id = get_default_content_id( $content_id );
	if ( ! $content_id ) {
		return [];
	}

	return MultilingualPress::resolve( 'multilingualpress.content_relations' )->get_relations(
		$site_id ?: get_current_blog_id(),
		$content_id,
		(string) $type
	);
}

/**
 * Returns all translations for the content element with the given ID.
 *
 * @since 3.0.0
 *
 * @param int $content_id Optional. Content ID. Defaults to 0.
 *
 * @return array[] An array with site IDs as keys and arrays with translation data as values.
 */
function get_translations( $content_id = 0 ) {

	if ( ! is_singular() && ! is_tag() && ! is_category() && ! is_tax() ) {
		return [];
	}

	$site_id = get_current_blog_id();

	$content_id = get_default_content_id( $content_id );

	$translations = MultilingualPress::resolve( 'multilingualpress.translations' )->get_translations( [
		'site_id'    => $site_id,
		'content_id' => $content_id,
	] );
	unset( $translations[ $site_id ] );
	if ( $translations ) {
		$translations = array_filter( $translations, function ( Translation $translation ) {

			return (bool) $translation->remote_url();
		} );
	}

	if ( ! $translations ) {
		return [];
	}

	$translations = array_map( function ( Translation $translation ) {

		$language = $translation->language();

		return [
			'post_id'        => $translation->target_content_id(),
			'post_title'     => $translation->remote_title(),
			'permalink'      => $translation->remote_url(),
			'flag'           => $translation->icon_url(),
			'lang'           => $language->name( 'lang' ),
			'language_short' => $language->name( 'lang' ),
			'language_long'  => $language->name( 'language_long' ),
		];
	}, $translations );

	return $translations;
}

/**
 * Checks if MultilingualPress debug mode is on.
 *
 * @since 3.0.0
 *
 * @return bool Whether or not MultilingualPress debug mode is on.
 */
function is_debug_mode() {

	return defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG;
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

	return MultilingualPress::resolve( 'multilingualpress.redirect_settings_repository' )->get_site_setting( $site_id );
}

/**
 * Checks if either MultilingualPress or WordPress script debug mode is on.
 *
 * @since 3.0.0
 *
 * @return bool Whether or not MultilingualPress or WordPress script debug mode is on.
 */
function is_script_debug_mode() {

	return is_debug_mode() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
}

/**
 * Checks if either MultilingualPress or WordPress debug mode is on.
 *
 * @since 3.0.0
 *
 * @return bool Whether or not MultilingualPress or WordPress debug mode is on.
 */
function is_wp_debug_mode() {

	return is_debug_mode() || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
}

/**
 * Returns the HTML string for the hidden nonce field according to the given nonce object.
 *
 * @since 3.0.0
 *
 * @param Nonce $nonce        Nonce object.
 * @param bool  $with_referer Optional. Render a referer field as well? Defaults to true.
 *
 * @return string The HTML string for the hidden nonce field according to the given nonce object.
 */
function nonce_field( Nonce $nonce, $with_referer = true ) {

	return sprintf(
		'<input type="hidden" name="%s" value="%s">%s',
		esc_attr( $nonce->action() ),
		esc_attr( (string) $nonce ),
		$with_referer ? wp_referer_field( false ) : ''
	);
}

/**
 * Redirects to the given URL (or the referer) after a settings update request.
 *
 * @since 3.0.0
 *
 * @param string $url     Optional. URL. Defaults to empty string.
 * @param string $setting Optional. Setting slug. Defaults to 'mlp-setting'.
 * @param string $code    Optional. Setting code for identification. Defaults to 'mlp-setting'.
 *
 * @return void
 */
function redirect_after_settings_update( $url = '', $setting = 'mlp-setting', $code = 'mlp-setting' ) {

	if ( $setting ) {
		add_settings_updated_message( $setting, $code );
	}

	if ( ! $url ) {
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			$url = $_POST['_wp_http_referer'] ?? '';
		}

		if ( ! $url ) {
			$url = $_REQUEST['_wp_http_referer'] ?? '';
		}
	}

	wp_safe_redirect( add_query_arg( 'settings-updated', true, $url ) );

	call_exit();
}

/**
 * Replaces in the language attributes for the html tag the WordPress language with the MultilingualPress language.
 *
 * @since   3.0.0
 * @wp-hook language_attributes
 *
 * @param string $language_attributes The language attributes for the html tag.
 *
 * @return string The language attributes for the html tag.
 */
function replace_language_in_language_attributes( $language_attributes ) {

	$site_language = \Inpsyde\MultilingualPress\get_current_site_language();
	if ( ! $site_language ) {
		return $language_attributes;
	}

	$language_attributes = preg_replace(
		'/(lang=[\"\'])' . get_bloginfo( 'language' ) . '([\"\'])/',
		'$1' . str_replace( '_', '-', $site_language ) . '$2',
		$language_attributes
	);

	return $language_attributes;
}

/**
 * Checks if the site with the given ID exists (within the current or given network) and is not marked as deleted.
 *
 * @since 3.0.0
 *
 * @param int $site_id    Site ID.
 * @param int $network_id Optional. Network ID. Defaults to 0.
 *
 * @return bool Wheter or not the site with the given ID exists and is not marked as deleted.
 */
function site_exists( $site_id, $network_id = 0 ) {

	static $cache = [];

	// We don't test large sites.
	if ( wp_is_large_network() ) {
		return true;
	}

	$network_id = (int) ( $network_id ?: get_current_network_id() );

	if ( ! isset( $cache[ $network_id ] ) ) {
		$db = MultilingualPress::resolve( 'multilingualpress.wpdb' );

		$query = $db->prepare( "SELECT blog_id FROM {$db->blogs} WHERE site_id = %d AND deleted = 0", $network_id );

		$cache[ $network_id ] = array_map( 'intval', $db->get_col( $query ) );
	}

	return in_array( (int) $site_id, $cache[ $network_id ], true );
}
