<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Api
 *
 * Not complete yet.
 *
 * @version 2014.04.11
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Api implements Mlp_Language_Api_Interface {

	/**
	 * @var Mlp_Language_Db_Access
	 */
	private $language_db;

	/**
	 * @var Inpsyde_Property_List_Interface
	 */
	private $data;

	/**
	 * Table name including base prefix.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 *@var Mlp_Site_Relations_Interface
	 */
	private $site_relations;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * @var array
	 */
	private $language_data_from_db = array();

	/**
	 * Constructor.
	 *
	 * @wp-hook plugins_loaded
	 * @param   Inpsyde_Property_List_Interface $data
	 * @param   string                          $table_name
	 * @param   Mlp_Site_Relations_Interface    $site_relations
	 * @param   Mlp_Content_Relations_Interface $content_relations
	 * @param   wpdb                            $wpdb
	 */
	public function __construct(
		Inpsyde_Property_List_Interface $data,
		$table_name,
		Mlp_Site_Relations_Interface    $site_relations,
		Mlp_Content_Relations_Interface $content_relations,
		wpdb                            $wpdb
	) {
		$this->data              = $data;
		$this->wpdb              = $wpdb;
		$this->language_db       = new Mlp_Language_Db_Access( $table_name );
		$this->table_name        = $this->wpdb->base_prefix . $table_name;
		$this->site_relations    = $site_relations;
		$this->content_relations = $content_relations;

		add_action( 'wp_loaded', array ( $this, 'load_language_manager' ) );
		add_filter( 'mlp_language_api', array ( $this, 'get_instance' ) );
	}

	/**
	 * (non-PHPdoc)
	 * @see Mlp_Language_Api_Interface::get_db()
	 */
	public function get_db() {
		return $this->language_db;
	}

	/**
	 * Access to this instance from the outside.
	 *
	 * Usage:
	 * <code>
	 * $mlp_language_api = apply_filters( 'mlp_language_api', NULL );
	 * if ( is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' ) ) {
	 *     // do something
	 * }
	 * </code>
	 */
	public function get_instance() {
		return $this;
	}

	/**
	 *
	 * @return void
	 */
	public function load_language_manager() {

		new Mlp_Language_Manager_Controller( $this->data, $this->language_db, $this->wpdb );
	}

	/**
	 * Get language names for related blogs.
	 *
	 * @see Mlp_Helpers::get_available_languages_titles()
	 * @param  int $base_site
	 * @return array
	 */
	public function get_site_languages( $base_site = 0 ) {

		$related_blogs = $options = array();
		$languages     = get_site_option( 'inpsyde_multilingual' );

		if ( 0 !== $base_site ) {

			$related_blogs = $this->get_related_sites( $base_site, TRUE );

			if ( empty ( $related_blogs ) )
				return array();
		}

		if ( ! is_array( $languages ) )
			return array ();

		foreach ( $languages as $site_id => $language_data ) {

			// Filter out blogs that are not related
			if ( ! in_array( $site_id, $related_blogs ) && 0 !== $base_site )
				continue;

			$lang = '';

			if ( isset ( $language_data[ 'text' ] ) )
				$lang = $language_data[ 'text' ];

			if ( empty ( $language_data[ 'lang' ] ) )
				continue;

			if ( '' === $lang )
				$lang = $this->get_lang_data_by_iso( $language_data[ 'lang' ] );

			$options[ $site_id ] = $lang;
		}

		return $options;
	}

	/**
	 * @param  string $iso Something like de_AT
	 *
	 * @param string $field the field which should be queried
	 * @return mixed
	 */
	public function get_lang_data_by_iso( $iso, $field = 'native_name' ) {

		$iso = str_replace( '_', '-', $iso );

		$query  = $this->wpdb->prepare(
			"SELECT `{$field}`
			FROM `{$this->table_name}`
			WHERE `http_name` = " . "%s LIMIT 1",
						   $iso
		);
		$result = $this->wpdb->get_var( $query );

		$return = NULL === $result ? '' : $result;

		return $return;
	}

	/**
	 * Ask for specific translations with arguments.
	 *
	 *
	 * @see prepare_translation_arguments()
	 * @param array $args {
	 *
	 *     Optional. If left out, some magic happens.
	 *
	 *     @type int    $site_id       Base site
	 *     @type int    $content_id    post or term_taxonomy ID, *not* term ID
	 *     @type string $type          @see Mlp_Language_Api::get_request_type()
	 *     @type bool   $strict        When TRUE (default) only matching exact
	 *                                 translations will be included
	 *     @type string $search_term   If you want to translate a search
	 *     @type string $post_type     For post type archives
	 *     @type bool   $include_base  Include the base site in returned list
	 *
	 * }
	 * @return array Array of Mlp_Translation instances, site IDs are the keys
	 */
	public function get_translations( Array $args = array() ) {

		/** @type WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		$arguments = $this->prepare_translation_arguments( $args );

		$sites = $this->get_related_sites(
			$arguments[ 'site_id' ],
			$arguments[ 'include_base' ]
		);

		if ( empty ( $sites ) )
			return array ();

		$content_relations = array();

		if ( ! empty ( $arguments[ 'content_id' ] ) ) {

			// array with site_ids as keys, content_ids as values
			$content_relations = $this->get_related_content_ids(
				$arguments[ 'site_id' ],
				$arguments[ 'content_id' ],
				$arguments[ 'type' ]
			);

			if ( empty ( $content_relations ) && $arguments[ 'strict' ] )
				return array ();
		}

		$translations      = array();
		$languages         = $this->get_all_language_data();

		foreach ( $sites as $site_id ) {

			if ( ! isset ( $languages[ $site_id ] ) )
				continue;

			$translations[ $site_id ] = array (
				'source_site_id'    => $arguments[ 'site_id' ],
				'target_site_id'    => $site_id,
				'type'              => $arguments[ 'type' ],
				'target_content_id' => 0,
				'target_title'      => ''
			);
		}

		reset( $translations );

		foreach ( $translations as $site_id => &$arr ) {

			$valid = TRUE;

			if ( ! empty ( $content_relations[ $site_id ] ) ) {

				$content_id                 = $content_relations[ $site_id ];
				$arr[ 'target_content_id' ] = $content_id;

				if ( 'term' === $arguments[ 'type' ] ) {

					$term_translation = new Mlp_Term_Translation( $this->wpdb, $wp_rewrite );
					$translation      = $term_translation->get_translation( $content_id, $site_id );

					if ( ! $translation )
						$valid = FALSE;
					else
						$arr = array_merge( $arr, $translation );
				}
				elseif ( 'post' === $arguments[ 'type' ] ) {

					switch_to_blog( $site_id );

					$translation = $this->get_post_translation(
						$content_relations[ $site_id ],
						isset( $arguments['post_status'] ) ? (array) $arguments['post_status'] : array(),
						$arguments['strict']
					);

					if ( ! $translation )
						$valid = FALSE;
					else
						$arr = array_merge( $arr, $translation );

					restore_current_blog();
				}
			}
			else {

				switch_to_blog( $site_id );

				if ( 'search' === $arguments[ 'type' ] ) {

					$url                 = get_search_link( $arguments[ 'search_term' ] );
					$arr[ 'target_url' ] = Mlp_Url_Factory::create( $url );
				}
				elseif ( 'post_type_archive' === $arguments[ 'type' ]
					&& ! empty ( $arguments[ 'post_type' ] )
				) {

					$translation = $this->get_post_type_archive_translation(
						$arguments[ 'post_type' ]
					);
					$arr = array_merge( $arr, $translation );
				}

				// Nothing found, use fallback if allowed
				if ( ( empty ( $arr[ 'target_url' ] ) && ! $arguments[ 'strict' ] )
					|| 'front_page' === $arguments[ 'type' ]
				) {
					$arr[ 'target_url' ] = get_home_url( $site_id, '/' );
				}

				if ( empty ( $arr[ 'target_url' ] ) )
					$valid = FALSE;

				restore_current_blog();
			}

			if ( ! $valid ) {
				unset ( $translations[ $site_id ] );
				continue;
			}

			$data = $languages[ $site_id ];

			if ( ! isset ( $data[ 'http_name' ] ) ) {
				if ( isset ( $data[ 'lang' ] ) )
					$data[ 'http_name' ] = $data[ 'lang' ];
				else
					$data[ 'http_name' ] = '';
			}

			if ( '' !== $data[ 'http_name' ] ) {
				$arr[ 'icon' ] = $this->get_flag_by_language(
					$data[ 'http_name' ],
					$site_id
				);
			} else {
				$arr[ 'icon' ] = '';
			}

			$arr['suppress_filters'] = $arguments['suppress_filters'];

			$arr = new Mlp_Translation( $arr, new Mlp_Language( $data ) );
		}

		/**
		 * Filter the translations before they are used.
		 *
		 * @param array $translations Translations.
		 * @param array $arguments    Translation arguments.
		 */
		$translations = apply_filters( 'mlp_translations', $translations, $arguments );

		return $translations;
	}

	/**
	 * Get translation for post type archive
	 *
	 * @param  string $post_type
	 * @return array
	 */
	public function get_post_type_archive_translation( $post_type ) {

		$return = array ();

		if ( ! $this->is_post_type_active( $post_type ) ) {
			return $return;
		}

		$url                    = get_post_type_archive_link( $post_type );
		$return[ 'target_url' ] = Mlp_Url_Factory::create( $url );
		$obj                    = get_post_type_object( $post_type );

		if ( $obj )
			$return[ 'target_title' ] = $obj->labels->name;

		return $return;
	}

	/**
	 * Get translation for posts of any post type.
	 *
	 * @param int      $content_id
	 * @param string[] $post_status Allowed post status.
	 * @param bool     $strict
	 *
	 * @return array|bool
	 */
	private function get_post_translation( $content_id, array $post_status, $strict ) {

		$post  = get_post( $content_id );

		if ( ! $post )
			return FALSE;

		if ( $post_status && ! in_array( $post->post_status, $post_status, true ) ) {
			return FALSE;
		}

		$title    = get_the_title( $content_id );
		$editable = current_user_can( 'edit_post', $content_id );

		// edit post screen
		if ( is_admin() ) {

			if ( ! $editable )
				return FALSE;

			return array (
				'target_title' => $title,
				'target_url'   => Mlp_Url_Factory::create( get_edit_post_link( $content_id ) )
			);
		}

		// frontend
		do_action( 'mlp_before_link' );
		$url = get_permalink( $content_id );
		do_action( 'mlp_after_link' );

		if ( 'publish' === $post->post_status || $editable )
			return array (
				'target_title' => $title,
				'target_url'   => empty ( $url ) ? '' : Mlp_Url_Factory::create( $url )
			);

		// unpublished post, not editable
		if ( $strict )
			return FALSE;

		return array (
			'target_title' => $title,
			'target_url'   => ''
		);
	}

	/**
	 * Get the flag URL for the given language.
	 *
	 * @param string $language Formatted like en_GB
	 * @param int    $site_id  Site ID.
	 *
	 * @return Mlp_Url_Interface
	 */
	public function get_flag_by_language( $language, $site_id = 0 ) {

		$custom_flag = get_blog_option( $site_id, 'inpsyde_multilingual_flag_url' );
		if ( $custom_flag ) {
			return Mlp_Url_Factory::create( $custom_flag );
		}

		$flag_path = $this->data->get( 'flag_path' );

		$language = str_replace( '-', '_', $language );
		$sub = strtok( $language, '_' );
		$file_name = $sub . '.gif';

		if ( is_readable( "$flag_path/$file_name" ) ) {
			return Mlp_Url_Factory::create( $this->data->get( 'flag_url' ) . $file_name );
		}

		return Mlp_Url_Factory::create( '' );
	}

	/**
	 * @return array
	 */
	private function get_all_language_data() {

		if ( ! empty ( $this->language_data_from_db ) )
			return $this->language_data_from_db;

		$languages = (array) get_site_option( 'inpsyde_multilingual', array() );

		if ( empty ( $languages ) )
			return array();

		$tags     = array ();
		$add_like = array ();

		foreach ( $languages as $site_id => $data ) {
			if ( ! empty ( $data[ 'lang' ] ) )
				$tags[ $site_id ] = str_replace('_', '-', $data[ 'lang' ] );
			elseif ( ! empty ( $data[ 'text' ] ) && preg_match( '~[a-zA-Z-]+~', $data[ 'text' ] ) )
				$tags[ $site_id ] = str_replace('_', '-', $data[ 'text' ] );

			// a site might have just 'EN' as text and no other values
			if ( isset( $tags[ $site_id ] ) && FALSE === strpos( $tags[ $site_id ], '-' ) ) {
				$tags[ $site_id ] = strtolower( $tags[ $site_id ] );
				$add_like[ $site_id ] = $tags[ $site_id ];
			}

			unset( $languages[ $site_id ]['lang'] );
		}

		$values = array_values( $tags );
		$values = "'" .  join( "','", $values ) . "'";

		$sql = "
SELECT `english_name`, `native_name`, `custom_name`, `is_rtl`, `http_name`, `priority`, `wp_locale`, `iso_639_1`
FROM $this->table_name
WHERE `http_name` IN( $values )";

		if ( ! empty ( $add_like ) ) {
			$sql .= " OR `iso_639_1` IN ('" . join( "','", array_values( $add_like ) ) . "')";
		}

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		foreach ( $tags as $site => $lang ) {

			foreach ( $results as $arr ) {
				if ( in_array( $lang, $arr, true ) ) {
					$languages[ $site ] += $arr;
				}
				elseif ( isset ( $add_like[ $site ] )
					&& $arr[ 'iso_639_1' ] === $add_like[ $site ]
				) {
					$languages[ $site ] += $arr;
				}
			}
		}

		$this->language_data_from_db = $languages;

		return $languages;
	}

	/**
	 * @param $language_data
	 *
	 * @return array
	 */
	private function get_language_tag( $language_data ) {

		$tag = $like = '';

		if ( ! empty( $language_data[ 'lang' ] ) ) {
			$tag = str_replace( '_', '-', $language_data[ 'lang' ] );
		} elseif (
			! empty( $language_data[ 'text' ] )
			&& preg_match( '~[a-zA-Z-]+~', $language_data[ 'text' ] )
		) {
			$tag = str_replace( '_', '-', $language_data[ 'text' ] );
		}

		// a site might have just 'EN' as text and no other values
		if ( FALSE === strpos( $tag, '-' ) ) {
			$tag = strtolower( $tag );
			$like = $tag;
		}

		return array( $tag, $like );
	}

	/**
	 * @uses get_request_type()
	 * @return string
	 */
	private function get_request_type() {

		$checks = array (
			'admin'             => 'is_admin',
			'post'              => array( $this, 'is_singular' ),
			'term'              => array( $this, 'is_term_archive_request' ),
			'post_type_archive' => 'is_post_type_archive',
			'search'            => 'is_search',
			'front_page'        => 'is_front_page',
		);

		foreach ( $checks as $type => $callback ) {

			if ( call_user_func( $callback ) ) {
				return $type;
			}
		}

		return '';
	}

	/**
	 * Check for regular singular pages and separate page for posts
	 *
	 * @return bool
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function is_singular() {

		if ( is_singular() ) {
			return TRUE;
		}

		return $this->is_separate_home_page();
	}

	/**
	 * Check for separate page for posts
	 *
	 * @return bool
	 */
	private function is_separate_home_page() {

		return is_home() && ! is_front_page();
	}

	/**
	 * @used-by get_request_type()
	 * @see     get_request_type()
	 * @return  bool
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function is_term_archive_request() {

		$queried_object = get_queried_object();

		if ( ! isset ( $queried_object->taxonomy ) )
			return FALSE;

		return isset ( $queried_object->name );
	}

	/**
	 * @param      $site_id
	 * @param bool $include_base
	 * @return array
	 */
	private function get_related_sites( $site_id, $include_base ) {

		if ( empty ( $site_id ) )
			$site_id = get_current_blog_id();

		$sites = $this->site_relations->get_related_sites( $site_id );

		if ( empty ( $sites ) )
			return array();

		if ( $include_base )
			$sites[] = $site_id;

		return $sites;
	}

	/**
	 * Get the current post type.
	 *
	 * When we have an archive with multiple post types, a custom query, we use
	 * just the first post type. This is not ideal, but easier to handle further
	 * down.
	 *
	 * @return string
	 */
	private function get_request_post_type() {

		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) )
			$post_type = reset( $post_type );

		return (string) $post_type;
	}

	/**
	 * @param array $args
	 * @return array
	 */
	private function prepare_translation_arguments( Array $args ) {

		$defaults = array(
			// always greater than 0
			'site_id'          => get_current_blog_id(),
			// 0 if missing
			'content_id'       => $this->get_query_id(),
			'type'             => $this->get_request_type(),
			'strict'           => true,
			'search_term'      => get_search_query(),
			'post_type'        => $this->get_request_post_type(),
			'include_base'     => false,
			'suppress_filters' => false,
			'post_status'      => array(),
		);

		$arguments = wp_parse_args( $args, $defaults );

		/**
		 * Filter the translation arguments.
		 *
		 * @param array $arguments Translation arguments.
		 */
		$arguments = apply_filters( 'mlp_get_translations_arguments', $arguments );

		return $arguments;
	}

	/**
	 * Get ID of queried object, post type or term.
	 *
	 * We need the term taxonomy ID for terms.
	 *
	 * @return int
	 */
	private function get_query_id() {

		if ( is_category() || is_tag() || is_tax() ) {
			return get_queried_object()->term_taxonomy_id;
		}

		return get_queried_object_id();
	}

	/**
	 * @param  array $arguments
	 * @return array
	 */
	private function prepare_translation_relations( Array $arguments ) {

		if ( ! $this->can_have_relations( $arguments ) )
			return array();

		$relations = $this->get_related_content_ids(
			$arguments[ 'site_id' ],
			$arguments[ 'content_id' ],
			$arguments[ 'type' ]
		);

		if ( 'post' !== $arguments[ 'type' ] || is_admin() )
			return $relations;

		return $this->remove_unpublished_posts( $relations );
	}

	/**
	 * Check if the current request is worth to be queried for relations
	 *
	 * @param array $arguments
	 * @return bool
	 */
	private function can_have_relations( Array $arguments ) {

		if ( empty ( $arguments[ 'content_id' ] ) )
			return FALSE;

		return in_array( $arguments['type'], array( 'post', 'term' ), true );
	}

	/**
	 * @param  array $relations
	 * @return array
	 */
	private function remove_unpublished_posts( Array $relations ) {

		foreach ( $relations as $site_id => $content_id ) {

			$post = get_blog_post( $site_id, $content_id );

			if ( ! $post || 'publish' !== $post->post_status )
				unset( $relations[ $site_id ] );
		}

		return $relations;
	}

	/**
	 * Returns an array with site ID as keys and content ID as values.
	 *
	 * @param  int    $site_id
	 * @param  int    $content_id
	 * @param  string $type
	 * @return array
	 */
	public function get_related_content_ids( $site_id, $content_id, $type ) {

		return $this->content_relations->get_relations(
			$site_id,
			$content_id,
			$type
		);
	}

	/**
	 * Checks if the given post type is active for MultilingualPress.
	 *
	 * @param string $post_type Post type name.
	 *
	 * @return bool Whether or not the given post type is active for MultilingualPress.
	 */
	private function is_post_type_active( $post_type ) {

		static $post_types;
		if ( ! isset( $post_types ) ) {
			/** This filter is documented in inc/post-translator/Mlp_Translation_Metabox.php */
			$post_types = (array) apply_filters( 'mlp_allowed_post_types', array( 'post', 'page' ) );
		}

		return in_array( $post_type, $post_types, true );
	}
}
