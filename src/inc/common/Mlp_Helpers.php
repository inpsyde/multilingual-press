<?php

/**
 * Various global helper methods
 *
 * Please use the functions in /inc/functions.php, do not access the methods of this class directly.
 *
 * @version 2015.06.26
 * @author  Inpsyde GmbH
 * @license GPL
 */
class Mlp_Helpers {

	/**
	 * @see Mlp_Helpers::insert_dependency()
	 * @type array
	 */
	private static $dependencies = array();

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
	public static function is_redirect( $blogid = false ) {

		if ( ! $blogid ) {
			$blogid = get_current_blog_id();
		}

		$redirect = get_blog_option( $blogid, 'inpsyde_multilingual_redirect' );

		return (bool) $redirect;
	}

	/**
	 * Get the language set by MultilingualPress.
	 *
	 * @param  bool $short
	 * @return string the language code
	 */
	public static function get_current_blog_language( $short = false ) {

		// Get all registered blogs
		$languages = get_site_option( 'inpsyde_multilingual' );

		// Get current blog
		$blogid = get_current_blog_id();

		// If this blog is in a language
		if ( ! isset( $languages[ $blogid ]['lang'] ) ) {
			return '';
		}

		if ( ! $short ) {
			return $languages[ $blogid ]['lang'];
		}

		return strtok( $languages[ $blogid ]['lang'], '_' );
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
	public static function get_available_languages( $not_related = false ) {

		$related_blogs = array();

		// Get all registered blogs
		$languages = get_site_option( 'inpsyde_multilingual' );

		if ( empty( $languages ) ) {
			return array();
		}

		/** @var Mlp_Site_Relations $site_relations */
		$site_relations = self::$dependencies['site_relations'];

		// Do we need related blogs only?
		if ( false === $not_related ) {
			$related_blogs = $site_relations->get_related_sites( get_current_blog_id() );

			// No related blogs? Leave here.
			if ( empty( $related_blogs ) ) {
				return array();
			}
		}

		$options = array();

		// Loop through blogs
		foreach ( $languages as $language_blogid => $language_data ) {

			// no blogs with a link to other blogs
			if ( empty( $language_data['lang'] ) || '-1' === $language_data['lang'] ) {
				continue;
			}

			$language_blogid = (int) $language_blogid;

			// Filter out blogs that are not related
			if ( ! $not_related && ! in_array( $language_blogid, $related_blogs, true ) ) {
				continue;
			}

			$lang = $language_data['lang'];

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
	public static function get_available_languages_titles( $related = true ) {

		/** @var Mlp_Language_Api $api */
		$api  = self::$dependencies['language_api'];
		$blog = $related ? get_current_blog_id() : 0;

		return $api->get_site_languages( $blog );
	}

	/**
	 * Get native name by ISO-639-1 code.
	 *
	 * @param string $iso Language code like "en" or "de"
	 * @param string $field the field which should be queried
	 *
	 * @return mixed
	 */
	public static function get_lang_by_iso( $iso, $field = 'native_name' ) {

		/** @var Mlp_Language_Api $api */
		$api  = self::$dependencies['language_api'];
		return $api->get_lang_data_by_iso( $iso, $field );
	}

	/**
	 * Get the element ID in other blogs for the selected element
	 *
	 * @param   int    $element_id ID of the selected element
	 * @param   string $type       | type of the selected element
	 * @param   int    $blog_id    ID of the selected blog
	 * @return  array $elements
	 */
	public static function load_linked_elements( $element_id = 0, $type = '', $blog_id = 0 ) {

		$element_id = self::get_default_content_id( $element_id );

		if ( ! $element_id ) {
			return array();
		}

		// If no ID is provided, get current blogs' ID
		if ( 0 === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		if ( '' === $type ) {
			$type = 'post';
		}

		/** @var Mlp_Language_Api $api */
		$api = self::$dependencies['language_api'];

		return $api->get_related_content_ids( $blog_id, $element_id, $type );
	}

	/**
	 * Get the element ID in other blogs for the selected element
	 * with additional information.
	 *
	 * @param  int $element_id
	 * @param  string $type Either 'post' or 'term'
	 * @return array $elements
	 */
	public static function get_interlinked_permalinks( $element_id = 0, $type = '' ) {

		if ( ! is_singular() && ! is_tag() && ! is_category() && ! is_tax() ) {
			return array();
		}

		$return     = array();
					  /** @var Mlp_Language_Api $api */
		$api        = self::$dependencies['language_api'];
		$site_id    = get_current_blog_id();
		$element_id = self::get_default_content_id( $element_id );

		$args = array(
			'site_id'    => $site_id,
			'content_id' => $element_id,
		);
		if ( '' !== $type ) {
			$args['type'] = $type;
		}

		// Array of Mlp_Translation instances, site IDs are the keys
		$related = $api->get_translations( $args );

		if ( empty( $related ) ) {
			return $return;
		}

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $related as $remote_site_id => $translation ) {

			if ( $site_id === (int) $remote_site_id ) {
				continue;
			}

			$url = $translation->get_remote_url();

			if ( empty( $url ) ) {
				continue;
			}

			$return[ $remote_site_id ] = array(
				'post_id'        => $translation->get_target_content_id(),
				'post_title'     => $translation->get_target_title(),
				'permalink'      => $url,
				'flag'           => $translation->get_icon_url(),
				/* 'lang' is the old entry, language_short the first part
				 * until the '_', long the complete language tag.
				 */
				'lang'           => $translation->get_language()->get_name( 'lang' ),
				'language_short' => $translation->get_language()->get_name( 'lang' ),
				'language_long'  => $translation->get_language()->get_name( 'language_long' ),
			);
		}

		return $return;
	}

	/**
	 * function for custom plugins to get activated on all language blogs
	 *
	 * @param   int    $element_id ID of the selected element
	 * @param   string $type       type of the selected element
	 * @param   int    $blog_id    ID of the selected blog
	 * @param   string $hook
	 * @param   mixed  $param
	 * @return  WP_Error|null
	 */
	public static function run_custom_plugin(
		/** @noinspection PhpUnusedParameterInspection */
		$element_id, $type, $blog_id, $hook, $param
	) {

		if ( empty( $element_id ) ) {
			return Mlp_WP_Error_Factory::create(
				'mlp_empty_custom_element',
				__( 'Empty Element', 'multilingual-press' )
			);
		}

		if ( empty( $type ) ) {
			return Mlp_WP_Error_Factory::create( 'mlp_empty_custom_type', __( 'Empty Type', 'multilingual-press' ) );
		}

		if ( empty( $hook ) || ! is_callable( $hook ) ) {
			return Mlp_WP_Error_Factory::create( 'mlp_empty_custom_hook', __( 'Invalid Hook', 'multilingual-press' ) );
		}

		// set the current element in the mlp class
		$languages    = mlp_get_available_languages();
		$current_blog_id = get_current_blog_id();

		if ( 0 === count( $languages ) ) {
			return null;
		}

		foreach ( $languages as $language_id => $language_name ) {

			if ( (int) $current_blog_id === (int) $language_id ) {
				continue;
			}

			switch_to_blog( $language_id );

			/**
			 * custom hook
			 * @param mixed $param
			 */
			do_action( $hook, $param );
			restore_current_blog();
		}

		return null;
	}

	/**
	 * Get the URL for the icon from a site ID
	 *
	 * @param  int    $site_id ID of a site
	 * @return string URL of the language image
	 */
	public static function get_language_flag( $site_id = 0 ) {

		if ( 0 === $site_id ) {
			$site_id = get_current_blog_id();
		}

		$languages = get_site_option( 'inpsyde_multilingual' );

		if ( empty( $languages[ $site_id ]['lang'] ) ) {
			return '';
		}

		/** @var Mlp_Language_Api $api */
		$api = self::$dependencies['language_api'];

		$url = (string) $api->get_flag_by_language( $languages[ $site_id ]['lang'], $site_id );

		return $url;
	}

	/**
	 * Get the language for a site
	 *
	 * @param    int $site_id ID of a blog
	 * @param  bool  $short   Return only the first part of the language code.
	 * @return    string Second part of language identifier
	 */
	public static function get_blog_language( $site_id = 0, $short = true ) {

		static $languages;

		if ( 0 === (int) $site_id ) {
			$site_id = get_current_blog_id();
		}

		if ( empty( $languages ) ) {
			$languages = get_site_option( 'inpsyde_multilingual' );
		}

		if ( empty( $languages[ $site_id ]['lang'] ) ) {
			return '';
		}

		if ( ! $short ) {
			return $languages[ $site_id ]['lang'];
		}

		return strtok( $languages[ $site_id ]['lang'], '_' );
	}

	/**
	 * Get the linked elements and display them as a list.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function show_linked_elements( $args ) {

		$defaults = array(
			'link_text'         => 'native',
			'display_flag'      => false,
			'sort'              => 'priority',
			'show_current_blog' => false,
			'strict'            => false, // get exact translations only
		);
		$params = wp_parse_args( $args, $defaults );

		// TODO: Eventually remove this, with version 2.2.0 + 4 at the earliest.
		switch ( $params['link_text'] ) {
			case 'text_flag':
				_doing_it_wrong(
					__METHOD__,
					"The value 'text_flag' for the argument 'link_text' is deprecated and will be removed in the future. Please use the value true for the argument 'display_flag', and choose one of the possible options for the argument 'link_text'.",
					'2.2.0'
				);

				$params['link_text'] = 'native';
				$params['display_flag'] = true;
				break;

			case 'flag':
				_doing_it_wrong(
					__METHOD__,
					"The value 'flag' for the argument 'link_text' is deprecated and will be removed in the future. Please use the value true for the argument 'display_flag', and the value 'none' for the argument 'link_text'.",
					'2.2.0'
				);

				$params['link_text'] = 'none';
				$params['display_flag'] = true;
				break;
		}

		/**
		 * Get the Language API object.
		 *
		 * @param Mlp_Language_Api_Interface $language_api Language API object.
		 */
		$api = apply_filters( 'mlp_language_api', null );
		/** @var Mlp_Language_Api_Interface $api */
		if ( ! is_a( $api, 'Mlp_Language_Api_Interface' ) ) {
			return '';
		}

		$translations_args = array(
			'strict'       => $params['strict'],
			'include_base' => $params['show_current_blog'],
		);
		$translations = $api->get_translations( $translations_args );
		if ( empty( $translations ) ) {
			return '';
		}

		$items = array();

		/** @var Mlp_Translation_Interface $translation */
		foreach ( $translations as $site_id => $translation ) {
			$url = $translation->get_remote_url();
			if ( empty( $url ) ) {
				continue;
			}

			$language = $translation->get_language();

			$items[ $site_id ] = array(
				'url'      => $url,
				'http'     => $language->get_name( 'http' ),
				'name'     => $language->get_name( $params['link_text'] ),
				'priority' => $language->get_priority(),
				'icon'     => (string) $translation->get_icon_url(),
			);
		}

		switch ( $params['sort'] ) {
			case 'blogid':
				ksort( $items );
				break;

			case 'priority':
				uasort( $items, array( __CLASS__, 'sort_priorities' ) );
				break;

			case 'name':
				uasort( $items, array( __CLASS__, 'strcasecmp_sort_names' ) );
				break;
		}

		$output = '<div class="mlp-language-box mlp_language_box"><ul>';

		foreach ( $items as $site_id => $item ) {
			$text = $item['name'];

			$img = ( ! empty( $item['icon'] ) && $params['display_flag'] )
				? '<img src="' . esc_url( $item['icon'] ) . '" alt="' . esc_attr( $item['name'] ) . '"> '
				: '';

			if ( get_current_blog_id() === $site_id ) {
				$output .= '<li><a class="current-language-item" href="">' . $img . esc_html( $text ) . '</a></li>';
			} else {
				$output .= sprintf(
					'<li><a rel="alternate" hreflang="%1$s" href="%2$s">%3$s%4$s</a></li>',
					esc_attr( $item['http'] ),
					esc_url( $item['url'] ),
					$img,
					esc_html( $text )
				);
			}
		}

		$output .= '</ul></div>';

		/**
		 * Filters the output of the linked elements.
		 *
		 * @since 2.5.0
		 *
		 * @param string  $output The generated HTML
		 * @param array[] $items  The language items
		 * @param array   $params The passed arguments (including missing defaults).
		 */
		$output = (string) apply_filters( 'mlp_linked_elements_html', $output, $items, $params );

		return $output;
	}

	/**
	 * Helper to sort languages.
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private static function strcasecmp_sort_names( array $a, array $b ) {

		return strcasecmp( $a['name'], $b['name'] );
	}

	/**
	 * Helper to sort languages.
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private static function sort_priorities( array $a, array $b ) {

		if ( $a['priority'] === $b['priority'] ) {
			return 0;
		}

		return ( $a['priority'] < $b['priority'] ) ? 1 : -1;
	}

	/**
	 * @param  int $content_id
	 * @return int
	 */
	private static function get_default_content_id( $content_id = 0 ) {

		if ( 0 < (int) $content_id ) {
			return $content_id;
		}

		return get_queried_object_id();
	}

	/**
	 * @param  string $name
	 * @param  object $instance
	 * @return void
	 */
	public static function insert_dependency( $name, $instance ) {

		self::$dependencies[ $name ] = $instance;
	}

}
