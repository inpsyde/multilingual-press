<?php # -*- coding: utf-8 -*-

/**
 * Relationships between content blocks (posts, terms, whatever).
 *
 * @version 2015.08.21
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Content_Relations implements Mlp_Content_Relations_Interface {

	/**
	 * @var string
	 */
	private $cache_group = 'mlp';

	/**
	 * @var string
	 */
	private $link_table;

	/**
	 * @var Mlp_Site_Relations_Interface
	 */
	private $site_relations;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param wpdb                         $wpdb           Database object.
	 * @param Mlp_Site_Relations_Interface $site_relations Site relations object.
	 * @param Mlp_Db_Table_Name_Interface  $link_table     Link table object.
	 */
	public function __construct(
		wpdb $wpdb,
		Mlp_Site_Relations_Interface $site_relations,
		$link_table
	) {

		$this->wpdb = $wpdb;
		$this->site_relations = $site_relations;
		$this->link_table = $link_table->get_name();
	}

	/**
	 * Set a relation according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return bool
	 */
	public function set_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type = 'post'
	) {

		$translation_ids = $this->get_translation_ids(
			$source_site_id,
			$target_site_id,
			$source_content_id,
			$target_content_id,
			$type
		);

		if ( $translation_ids[ 'ml_source_blogid' ] === $target_site_id ) {
			$target_site_id = $source_site_id;

			$target_content_id = $source_content_id;
		}

		$existing = $this->get_relations(
			$translation_ids[ 'ml_source_blogid' ],
			$translation_ids[ 'ml_source_elementid' ],
			$type
		);

		if ( isset( $existing[ $source_site_id ], $existing[ $target_site_id ] ) ) {
			if (
				$existing[ $source_site_id ] === $source_content_id
				&& $existing[ $target_site_id ] === $target_content_id
			) {
				return TRUE;
			}

			if ( $existing[ $source_site_id ] !== $source_content_id ) {
				$target_site_id = $source_site_id;

				$target_content_id = $source_content_id;
			}

			$this->delete_relation(
				$translation_ids[ 'ml_source_blogid' ],
				$target_site_id,
				$translation_ids[ 'ml_source_elementid' ],
				0,
				$type
			);
		} elseif ( isset( $existing[ $target_site_id ] ) ) {
			$target_site_id = $source_site_id;

			$target_content_id = $source_content_id;
		}

		$result = (bool) $this->insert_row(
			$translation_ids[ 'ml_source_blogid' ],
			$target_site_id,
			$translation_ids[ 'ml_source_elementid' ],
			$target_content_id,
			$type
		);

		$cache_key = $this->get_cache_key(
			$translation_ids[ 'ml_source_blogid' ],
			$translation_ids[ 'ml_source_elementid' ],
			$type
		);
		wp_cache_delete( $cache_key, $this->cache_group );

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * Return the cache key for the given arguments.
	 *
	 * @param int    $source_site_id    Blog ID.
	 * @param int    $source_content_id Content ID.
	 * @param string $type              Content type.
	 *
	 * @return string
	 */
	private function get_cache_key( $source_site_id, $source_content_id, $type ) {

		return "mlp_{$type}_relations_{$source_site_id}_{$source_content_id}";
	}

	/**
	 * Return an array with site IDs as keys and content IDs as values.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	public function get_relations( $source_site_id, $source_content_id, $type = 'post' ) {

		$cache_key = $this->get_cache_key( $source_site_id, $source_content_id, $type );

		$cache = wp_cache_get( $cache_key, $this->cache_group );
		if ( is_array( $cache ) ) {
			return $cache;
		}

		$sql = "
SELECT DISTINCT t.ml_blogid as site_id, t.ml_elementid as content_id
FROM {$this->link_table} s
INNER JOIN {$this->link_table} t
ON s.ml_source_blogid = t.ml_source_blogid
	AND s.ml_source_elementid = t.ml_source_elementid
	AND s.ml_type = t.ml_type
WHERE s.ml_blogid = %d
	AND s.ml_elementid = %d
	AND s.ml_type = %s";

		$query = $this->wpdb->prepare( $sql, $source_site_id, $source_content_id, $type );

		$results = $this->wpdb->get_results( $query, ARRAY_A );
		if ( ! $results ) {
			return array();
		}

		$output = array();

		foreach ( $results as $set ) {
			$output[ (int) $set[ 'site_id' ] ] = (int) $set[ 'content_id' ];
		}

		wp_cache_set( $cache_key, $output, $this->cache_group );

		return $output;
	}

	/**
	 * Delete a relation according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return int Number of deleted rows
	 */
	public function delete_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id = 0,
		$type = 'post'
	) {

		$where = array(
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id,
			'ml_type'             => $type,
		);
		$where_format = array(
			'%d',
			'%d',
			'%s',
		);

		if ( 0 < $target_site_id ) {
			$where[ 'ml_blogid' ] = $target_site_id;
			$where_format[] = '%d';
		}

		if ( 0 < $target_content_id ) {
			$where[ 'ml_elementid' ] = $target_content_id;
			$where_format[] = '%d';
		}

		$result = (int) $this->wpdb->delete( $this->link_table, $where, $where_format );

		$cache_key = $this->get_cache_key( $source_site_id, $source_content_id, $type );
		wp_cache_delete( $cache_key, $this->cache_group );

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * Return the existing (or new) translation IDs according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	public function get_translation_ids(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	) {

		$result = $this->get_existing_translation_ids(
			$source_site_id,
			$target_site_id,
			$source_content_id,
			$target_content_id,
			$type
		);

		if ( ! $result ) {
			return $this->get_new_translation_ids( $source_site_id, $source_content_id, $type );
		}

		if ( 1 < count( $result ) ) {
			// We have more than one id.
			$this->clean_up_duplicated_translation_ids( $result, $type );
		}

		return $result[ 0 ];
	}

	/**
	 * Insert a row into the link table.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return int
	 */
	private function insert_row(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	) {

		$result = (int) $this->wpdb->insert(
			$this->link_table,
			array(
				'ml_source_blogid'    => $source_site_id,
				'ml_source_elementid' => $source_content_id,
				'ml_blogid'           => $target_site_id,
				'ml_elementid'        => $target_content_id,
				'ml_type'             => $type,
			)
		);

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * Return new translation IDs for the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	private function get_new_translation_ids( $source_site_id, $source_content_id, $type ) {

		$this->insert_row(
			$source_site_id,
			$source_site_id,
			$source_content_id,
			$source_content_id,
			$type
		);

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return array(
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id,
		);
	}

	/**
	 * Delete duplicate database entries.
	 *
	 * @param array  $relations Content relations
	 * @param string $type      Content type.
	 *
	 * @return int
	 */
	private function clean_up_duplicated_translation_ids( array $relations, $type ) {

		$result = (int) $this->wpdb->update(
			$this->link_table,
			array(
				'ml_source_blogid'    => $relations[ 0 ][ 'ml_source_blogid' ],
				'ml_source_elementid' => $relations[ 0 ][ 'ml_source_elementid' ],
			),
			array(
				'ml_source_blogid'    => $relations[ 1 ][ 'ml_source_blogid' ],
				'ml_source_elementid' => $relations[ 1 ][ 'ml_source_elementid' ],
				'ml_type'             => $type,
			),
			array(
				'%d',
				'%d',
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);

		do_action(
			'mlp_debug',
			current_filter()
			. '/' . __METHOD__ . '/' . __LINE__
			. " - {$this->wpdb->last_query}"
		);

		return $result;
	}

	/**
	 * Return the existing translation IDs according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	public function get_existing_translation_ids(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	) {

		$sql = "
SELECT DISTINCT ml_source_blogid, ml_source_elementid
FROM {$this->link_table}
WHERE (
		( ml_blogid = %d AND ml_elementid = %d )
		OR ( ml_blogid = %d AND ml_elementid = %d )
	)
	AND ml_type = %s";

		$query = $this->wpdb->prepare(
			$sql,
			$source_site_id,
			$source_content_id,
			$target_site_id,
			$target_content_id,
			$type
		);

		$result = $this->wpdb->get_results( $query, ARRAY_A );
		if ( ! $result ) {
			return array();
		}

		foreach ( $result as $key => $data ) {
			$result[ $key ] = array_map( 'intval', $data );
		}

		return $result;
	}

	/**
	 * Return the term taxonomy ID of the given target site for the given source term.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return int
	 */
	public function get_element_for_site( $source_site_id, $target_site_id, $source_content_id, $type ) {

		$sql = "
SELECT t.ml_elementid
FROM {$this->link_table} s
INNER JOIN {$this->link_table} t
ON s.ml_source_blogid = t.ml_source_blogid
	AND s.ml_source_elementid = t.ml_source_elementid
	AND s.ml_type = t.ml_type
WHERE s.ml_id != t.ml_id
	AND s.ml_blogid = %d
	AND s.ml_elementid = %d
	AND s.ml_type = %s
	AND t.ml_blogid = %d
LIMIT 1";

		$query = $this->wpdb->prepare(
			$sql,
			$source_site_id,
			$source_content_id,
			$type,
			$target_site_id
		);

		return (int) $this->wpdb->get_var( $query );
	}

}
