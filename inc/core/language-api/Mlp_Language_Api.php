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
	 * @type Mlp_Content_Relations_Interface
	 */
	private $content_relations;

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
	 * if ( is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' ) )
	 * {
	 *     // do something
	 * }
	 * </code>
	 */
	public function get_instance() {
		return $this;
	}

	public function load_language_manager()
	{
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

		static $languages;

		$related_blogs = '';

		if ( empty ( $languages ) )
			$languages = get_site_option( 'inpsyde_multilingual' );

		if ( 0 !== $base_site ) {

			$related_blogs = $this->get_related_sites( $base_site );

			if ( empty ( $related_blogs ) )
				return array();
		}

		if ( ! is_array( $languages ) )
			return array ();

		$options = array ();
		$related_blogs[ ] = get_current_blog_id();

		foreach ( $languages as $language_blogid => $language_data ) {

			// Filter out blogs that are not related
			if ( is_array( $related_blogs ) && ! in_array( $language_blogid, $related_blogs ) && 0 !== $base_site )
				continue;

			$lang = '';

			if ( isset ( $language_data[ 'text' ] ) )
				$lang = $language_data[ 'text' ];

			if ( empty ( $language_data[ 'lang' ] ) )
				continue;

			if ( '' === $lang )
				$lang = $this->get_lang_data_by_iso( $language_data[ 'lang' ] );

			$options[ $language_blogid ] = $lang;
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
	 * Possible arguments are:
	 *
	 *     - 'site_id'              Base site
	 *
	 *     - 'content_id'           post or term_taxonomy ID, *not* term ID
	 *
	 *     - 'type'                 see Mlp_Language_Api::get_request_type(),
	 *
	 *     - 'strict'               When TRUE (default) only matching exact
	 *                                  translations will be included
	 *
	 *     - 'search_term'          if you want to translate a search
	 *
	 *     - 'post_type'            for post type archives
	 *
	 *     - 'include_base'         bool. Include the base site in returned list
	 *
	 * @param  array $args Optional. If left out, some magic happens.
	 * @return array Array of Mlp_Translation instances, site IDs are the keys
	 */
	public function get_translations( Array $args = array() ) {

		$arguments = $this->prepare_translation_arguments( $args );
		$languages = $this->get_all_language_data();
		$sites     = $this->get_related_sites( $arguments[ 'site_id' ] );

		if ( empty ( $languages ) || empty ( $sites ) )
			return array();

		if ( $arguments[ 'include_base' ] )
			$sites[] = $arguments[ 'site_id' ];

		$relations = $this->prepare_translation_relations( $arguments );

		$out = array();

		foreach ( $languages as $site_id => $data ) {

			if ( ! in_array( $site_id, $sites ) )
				continue;

			$out[ $site_id ] = $this->build_translation_object(
				$arguments,
				$relations,
				$data,
				$site_id
			);
		}

		return $out;
	}

	/**
	 * @param  array $arguments
	 * @param  array $relations
	 * @param  array $data
	 * @param  int   $site_id
	 * @return Mlp_Translation_Interface
	 */
	private function build_translation_object(
		Array $arguments,
		Array $relations,
		Array $data,
		      $site_id
	) {

		$target_content_id = 0;

		if ( ! empty ( $relations[ $site_id ] ) )
			$target_content_id = $relations[ $site_id ];

		list ( $url, $target_title ) = $this->get_remote_address(
			$site_id,
			$arguments,
			$relations
		);

		if ( ! isset ( $data[ 'http_name' ] ) ) {
			if ( isset ( $data[ 'lang' ] ) )
				$data[ 'http_name' ] = $data[ 'lang' ];
			else
				$data[ 'http_name' ] = '';
		}

		$icon = $this->get_flag_by_language( $data[ 'http_name' ], $site_id );

		$params = array (
			'source_site_id'    => $arguments[ 'site_id' ],
			'target_site_id'    => $site_id,
			'target_content_id' => $target_content_id,
			'target_title'      => $target_title,
			'target_url'        => $url,
			'type'              => $arguments[ 'type' ],
			'icon'              => $icon
		);

		return new Mlp_Translation( $params, new Mlp_Language( $data ) );
	}

	/**
	 * @param  string $language Formatted like en_GB
	 * @param  int    $site_id
	 * @return Mlp_Url_Interface
	 */
	public function get_flag_by_language( $language, $site_id = 0 ) {

		$custom_flag = get_blog_option( $site_id, 'inpsyde_multilingual_flag_url' );

		if ( $custom_flag )
			return new Mlp_Url( $custom_flag );

		$language  = str_replace( '-', '_', $language );
		$sub       = strtok( $language, '_' );
		$file_name = $sub . '.gif';

		if ( is_readable( "{$this->data->flag_path}/$file_name" ) )
			return new Mlp_Url( $this->data->flag_url . $file_name );

		return new Mlp_Url( '' );
	}

	/**
	 * @param  int   $site_id
	 * @param  array $arguments Passed by reference
	 * @param  array $relations
	 * @return array Mlp_Url and title
	 */
	private function get_remote_address( $site_id, Array &$arguments, Array $relations ) {

		$url = $title = '';

		if ( $site_id !== get_current_blog_id() )
			switch_to_blog( $site_id );

		if ( 'term' === $arguments[ 'type' ]
			&& ! empty ( $relations[ $site_id ] )
		) {

			$term  = $this->get_term_by_tt_id( $relations[ $site_id ] );
			$title = $term[ 'name' ];
			$url   = get_term_link( (int) $term[ 'term_id' ], $term[ 'taxonomy' ] );
		}
		elseif ( 'post' === $arguments[ 'type' ]
			&& ! empty ( $relations[ $site_id ] )
		) {

			$url   = (string) get_permalink( $relations[ $site_id ] );
			$title = get_the_title( $relations[ $site_id ] );
		}
		elseif ( 'post_type_archive' === $arguments[ 'type' ]
			&& ! empty ( $arguments[ 'post_type' ] )
		) {
			$url = get_post_type_archive_link( $arguments[ 'post_type' ] );
			$obj = get_post_type_object( $arguments[ 'post_type' ] );

			if ( $obj )
				$title = $obj->labels->name;
		}
		elseif ( 'home' === $arguments[ 'type' ] ) {
			$url = get_home_url( $site_id );
		}
		elseif ( 'search'  === $arguments[ 'type' ]
			&& ! empty ( $arguments[ 'search_term' ] )
		) {
			$url = get_search_link( $arguments[ 'search_term' ] );
		}

		// Nothing found, use fallback if allowed
		if ( ( '' === $url && ! $arguments[ 'strict' ] )
			|| 'front_page' === $arguments[ 'type' ]
		) {
			$url = get_site_url( $site_id, '/' );
		}

		restore_current_blog();

		return array ( new Mlp_Url( $url ), $title );
	}

	/**
	 * @return array
	 */
	private function get_all_language_data() {

		$languages = (array) get_site_option( 'inpsyde_multilingual', array() );

		if ( empty ( $languages ) )
			return array();

		$tags = array();
		$add_like = array();

		foreach ( $languages as $site_id => $data ) {
			if ( ! empty ( $data[ 'lang' ] ) )
				$tags[ $site_id ] = str_replace('_', '-', $data[ 'lang' ] );
			elseif ( ! empty ( $data[ 'text' ] ) && preg_match( '~[a-zA-Z-]+~', $data[ 'text' ] ) )
				$tags[ $site_id ] = str_replace('_', '-', $data[ 'text' ] );

			// a site might have just 'EN' as text and no other values
			if ( FALSE === strpos( $tags[ $site_id ], '-' ) ) {
				$tags[ $site_id ] = strtolower( $tags[ $site_id ] );
				$add_like[ $site_id ] = $tags[ $site_id ];
			}
		}

		$values = array_values( $tags );
		$values = "'" .  join( "','", $values ) . "'";

		$sql = "
SELECT `english_name`, `native_name`, `custom_name`, `is_rtl`, `http_name`, `priority`, `wp_locale`, `iso_639_1`
FROM $this->table_name
WHERE `http_name` IN( $values )";

		if ( ! empty ( $add_like ) ) {
			$sql .= " OR `iso_639_1` IN ('" . join("','", array_values( $add_like )) . "')";
		}

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		foreach ( $tags as $site => $lang ) {

			foreach ( $results as $arr ) {
				if ( in_array( $lang, $arr ) ) {
					$languages[ $site ] += $arr;
				}
				elseif ( isset ( $add_like[ $site ] )
					&& $arr[ 'iso_639_1' ] === $add_like[ $site ]
				) {
					$languages[ $site ] += $arr;
				}
			}
		}

		return $languages;
	}

	/**
	 * Get a term by its term_taxonomy_id.
	 *
	 * @param  int $tt_id term_taxonomy_id
	 * @return array
	 */
	private function get_term_by_tt_id( $tt_id ) {

		$sql = "
SELECT terms.`term_id`, terms.`name`, terms.`slug`, tax.`taxonomy`
FROM {$this->wpdb->terms} terms
  INNER JOIN {$this->wpdb->term_taxonomy} tax
    ON tax.`term_taxonomy_id` = %d
WHERE tax.`term_id` = terms.`term_id`
LIMIT 1";

		$query  = $this->wpdb->prepare( $sql, $tt_id );
		$result = $this->wpdb->get_row( $query, ARRAY_A );

		// $result might be NULL, but we need a predictable return type.
		return empty ( $result ) ? array() : $result;
	}

	/**
	 * @return string
	 */
	private function get_request_type() {

		if ( is_singular() )
			return 'post';

		if ( $this->is_term_archive_request() )
			return 'term';

		if ( is_post_type_archive() )
			return 'post_type_archive';

		if ( is_search() )
			return 'search';

		if ( is_front_page() )
			return 'front_page';

		if ( is_home() )
			return 'home';

		return '';
	}

	/**
	 * @return bool
	 */
	private function is_term_archive_request() {

		$queried_object = get_queried_object();

		if ( ! isset ( $queried_object->taxonomy ) )
			return FALSE;

		return isset ( $queried_object->name );
	}

	/**
	 * @param $site_id
	 * @return array
	 */
	private function get_related_sites( $site_id ) {

		if ( empty ( $site_id ) )
			$site_id = get_current_blog_id();

		return $this->site_relations->get_related_sites(
			$site_id,
			! is_user_logged_in()
		);
	}

	/**
	 * @return string
	 */
	private function get_request_post_type() {

		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) )
			$post_type = reset( $post_type );

		return $post_type;
	}

	/**
	 * @param array $args
	 * @return array
	 */
	private function prepare_translation_arguments( Array $args ) {

		$defaults = array (
			// always greater than 0
			'site_id'              => get_current_blog_id(),
			// 0 if missing
			'content_id'           => get_queried_object_id(),
			'type'                 => $this->get_request_type(),
			'strict'               => TRUE,
			'search_term'          => get_search_query(),
			'post_type'            => $this->get_request_post_type(),
			'include_base'         => FALSE
		);

		$arguments = wp_parse_args( $args, $defaults );

		return $arguments;
	}

	/**
	 * @param $arguments
	 * @return array
	 */
	private function prepare_translation_relations( $arguments ) {

		if ( empty ( $arguments[ 'content_id' ] ) )
			return array();

		if ( ! in_array( $arguments[ 'type' ], array ( 'term', 'post' ) ) )
			return array();

		return $this->get_related_content_ids(
			$arguments[ 'site_id' ],
			$arguments[ 'content_id' ],
			$arguments[ 'type' ]
		);
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
}