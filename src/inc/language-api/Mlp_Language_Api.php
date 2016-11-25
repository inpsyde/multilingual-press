<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Type\Translation;
use Inpsyde\MultilingualPress\Common\Request;
use Inpsyde\MultilingualPress\Factory\TypeFactory;

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
	 *@var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var array
	 */
	private $language_data_from_db = [];

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor.
	 *
	 * @wp-hook plugins_loaded
	 *
	 * @param   Inpsyde_Property_List_Interface $data
	 * @param   string                          $table_name
	 * @param   SiteRelations                   $site_relations
	 * @param   ContentRelations                $content_relations
	 * @param   wpdb                            $wpdb
	 * @param   TypeFactory                     $type_factory Type factory object.
	 * @param Request                           $request      Request object.
	 */
	public function __construct(
		Inpsyde_Property_List_Interface $data,
		$table_name,
		SiteRelations    $site_relations,
		ContentRelations $content_relations,
		wpdb                            $wpdb,
		TypeFactory                     $type_factory,
		Request $request
	) {

		$this->data              = $data;
		$this->wpdb              = $wpdb;
		$this->table_name        = $this->wpdb->base_prefix . $table_name;
		$this->site_relations    = $site_relations;
		$this->content_relations = $content_relations;
		$this->type_factory = $type_factory;

		$this->request = $request;

		add_action( 'wp_loaded', function () {

			new Mlp_Language_Manager_Controller(
				$this->data,
				new Mlp_Language_Db_Access( $this->table_name ),
				$this->wpdb
			);
		} );
	}

	/**
	 * Ask for specific translations with arguments.
	 *
	 *
	 * @see prepare_translation_arguments()
	 *
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
	 * @return Translation[] Array of Mlp_Translation instances, site IDs are the keys
	 */
	public function get_translations( array $args = [] ) {

		$arguments = $this->prepare_translation_arguments( $args );

		$key = md5( serialize( $arguments ) );

		$cached = wp_cache_get( $key, 'mlp' );
		if ( is_array( $cached ) )
			return $cached;

		$sites = $this->site_relations->get_related_site_ids(
			$arguments[ 'site_id' ],
			$arguments[ 'include_base' ]
		);

		if ( empty ( $sites ) )
			return [];

		$content_relations = [];

		if ( ! empty ( $arguments[ 'content_id' ] ) ) {

			// array with site_ids as keys, content_ids as values
			$content_relations = $this->content_relations->get_relations(
				$arguments[ 'site_id' ],
				$arguments[ 'content_id' ],
				$arguments[ 'type' ]
			);

			if ( empty ( $content_relations ) && $arguments[ 'strict' ] )
				return [];
		}

		$translations      = [];
		$languages         = $this->get_all_language_data();

		foreach ( $sites as $site_id ) {

			if ( ! isset ( $languages[ $site_id ] ) )
				continue;

			$translations[ $site_id ] = [
				'remote_title'      => '',
				'source_site_id'    => $arguments['site_id'],
				'target_site_id'    => $site_id,
				'target_content_id' => 0,
				'type'              => $arguments['type'],
			 ];
		}

		reset( $translations );

		/** @type WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		foreach ( $translations as $site_id => &$arr ) {

			$valid = TRUE;

			if ( ! empty ( $content_relations[ $site_id ] ) ) {

				$content_id                 = $content_relations[ $site_id ];
				$arr[ 'target_content_id' ] = $content_id;

				if ( 'term' === $arguments[ 'type' ] ) {

					$term_translation = new Mlp_Term_Translation( $this->wpdb, $wp_rewrite, $this->type_factory );
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
						$arguments[ 'strict' ]
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
					$arr['remote_url'] = $this->type_factory->create_url( [
						get_search_link( $arguments['search_term'] ),
					] );
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
				if ( ( empty ( $arr[ 'remote_url' ] ) && ! $arguments[ 'strict' ] )
					|| 'front_page' === $arguments[ 'type' ]
				) {
					$arr[ 'remote_url' ] = $this->type_factory->create_url( [
						get_site_url( $site_id, '/' ),
					] );
				}

				if ( empty ( $arr[ 'remote_url' ] ) )
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
				$arr[ 'icon_url' ] = \Inpsyde\MultilingualPress\get_flag_url_for_site( $site_id );
			} else {
				$arr[ 'icon_url' ] = $this->type_factory->create_url( [
					'',
				] );
			}

			$arr['suppress_filters'] = $arguments['suppress_filters'];

			$arr = $this->type_factory->create_translation( [
				$arr,
				$this->type_factory->create_language( [
					$data,
				] ),
			] );
		}

		/**
		 * Filter the translations before they are used.
		 *
		 * @param Translation[] $translations Translations.
		 * @param array         $arguments    Translation arguments.
		 */
		$translations = apply_filters( 'mlp_translations', $translations, $arguments );
		wp_cache_set( $key, $translations, 'mlp' );

		// TODO: In deprecated class, add "target_*" aliases for elements in $translations with "remote_*" keys.

		return $translations;
	}

	/**
	 * Get translation for post type archive
	 *
	 * @param  string $post_type
	 * @return array
	 */
	private function get_post_type_archive_translation( $post_type ) {

		$return = [
			'remote_url' => $this->type_factory->create_url( [
				get_post_type_archive_link( $post_type ),
			] ),
		];

		$post_type_object = get_post_type_object( $post_type );
		if ( $post_type_object ) {
			$return['remote_title'] = $post_type_object->labels->name;
		}

		return $return;
	}

	/**
	 * Get translation for posts of any post type.
	 *
	 * @param  int  $content_id
	 * @param  bool $strict
	 * @return array|bool
	 */
	private function get_post_translation( $content_id, $strict ) {

		$post  = get_post( $content_id );

		if ( ! $post )
			return FALSE;

		$title    = get_the_title( $content_id );
		$editable = current_user_can( 'edit_post', $content_id );

		// edit post screen
		if ( is_admin() ) {

			if ( ! $editable )
				return FALSE;

			return [
				'remote_title' => $title,
				'remote_url'   => $this->type_factory->create_url( [
					get_edit_post_link( $content_id ),
				] ),
			];
		}

		// frontend
		do_action( 'mlp_before_link' );
		$url = get_permalink( $content_id );
		do_action( 'mlp_after_link' );

		if ( 'publish' === $post->post_status || $editable )
			return [
				'remote_title' => $title,
				'remote_url'   => $this->type_factory->create_url( [
					$url ?: '',
				] ),
			];

		// unpublished post, not editable
		if ( $strict )
			return FALSE;

		return [
			'remote_title' => $title,
			'remote_url'   => ''
		 ];
	}

	/**
	 * @return array
	 */
	private function get_all_language_data() {

		if ( ! empty ( $this->language_data_from_db ) )
			return $this->language_data_from_db;

		$languages = (array) get_site_option( 'inpsyde_multilingual', [] );

		if ( empty ( $languages ) )
			return [];

		$tags     = [];
		$add_like = [];

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
	 * @param array $args
	 * @return array
	 */
	private function prepare_translation_arguments( array $args ) {

		$arguments = wp_parse_args( $args, [
			// always greater than 0
			'site_id'          => get_current_blog_id(),
			// 0 if missing
			'content_id'       => $this->request->queried_object_id(),
			'type'             => $this->request->type(),
			'strict'           => true,
			'search_term'      => get_search_query(),
			'post_type'        => $this->request->post_type(),
			'include_base'     => false,
			'suppress_filters' => false,
		] );

		/**
		 * Filter the translation arguments.
		 *
		 * @param array $arguments Translation arguments.
		 */
		$arguments = apply_filters( 'mlp_get_translations_arguments', $arguments );

		return $arguments;
	}
}
