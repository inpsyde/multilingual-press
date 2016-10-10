<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Database\Table;
use wpdb;

// TODO: For now, this is just a copy of the old API. Functionally refactor this as soon as the structural one is done.

/**
 * Content relations API implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class WPDBContentRelations implements ContentRelations {

	/**
	 * @var string
	 */
	private $cache_group = 'mlp';

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var string
	 */
	private $table;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Table         $table          Content relations table object.
	 * @param SiteRelations $site_relations Site relations API object.
	 */
	public function __construct( Table $table, SiteRelations $site_relations ) {

		$this->table = $table->name();

		$this->site_relations = $site_relations;

		$this->db = $GLOBALS['wpdb'];
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

		$where        = [
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id,
			'ml_type'             => $type,
		];
		$where_format = [
			'%d',
			'%d',
			'%s',
		];

		if ( 0 < $target_site_id ) {
			$where['ml_blogid'] = $target_site_id;
			$where_format[]     = '%d';
		}

		if ( 0 < $target_content_id ) {
			$where['ml_elementid'] = $target_content_id;
			$where_format[]        = '%d';
		}

		$result = (int) $this->db->delete( $this->table, $where, $where_format );

		$cache_key = $this->get_cache_key( $source_site_id, $source_content_id, $type );
		wp_cache_delete( $cache_key, $this->cache_group );

		\Inpsyde\MultilingualPress\debug(
			current_filter() . '/' . __METHOD__ . '/' . __LINE__ . " - {$this->db->last_query}"
		);

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
FROM {$this->table} s
INNER JOIN {$this->table} t
ON s.ml_source_blogid = t.ml_source_blogid
	AND s.ml_source_elementid = t.ml_source_elementid
	AND s.ml_type = t.ml_type
WHERE s.ml_id != t.ml_id
	AND s.ml_blogid = %d
	AND s.ml_elementid = %d
	AND s.ml_type = %s
	AND t.ml_blogid = %d
LIMIT 1";

		$query = $this->db->prepare(
			$sql,
			$source_site_id,
			$source_content_id,
			$type,
			$target_site_id
		);

		return (int) $this->db->get_var( $query );
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
FROM {$this->table}
WHERE (
		( ml_blogid = %d AND ml_elementid = %d )
		OR ( ml_blogid = %d AND ml_elementid = %d )
	)
	AND ml_type = %s";

		$query = $this->db->prepare(
			$sql,
			$source_site_id,
			$source_content_id,
			$target_site_id,
			$target_content_id,
			$type
		);

		$result = $this->db->get_results( $query, ARRAY_A );
		if ( ! $result ) {
			return [];
		}

		foreach ( $result as $key => $data ) {
			$result[ $key ] = array_map( 'intval', $data );
		}

		return $result;
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
SELECT t.ml_blogid as site_id, t.ml_elementid as content_id
FROM {$this->table} s
INNER JOIN {$this->table} t
ON s.ml_source_blogid = t.ml_source_blogid
	AND s.ml_source_elementid = t.ml_source_elementid
	AND s.ml_type = t.ml_type
WHERE s.ml_blogid = %d
	AND s.ml_elementid = %d
	AND s.ml_type = %s";

		$query = $this->db->prepare( $sql, $source_site_id, $source_content_id, $type );

		$results = $this->db->get_results( $query, ARRAY_A );
		if ( ! $results ) {
			return [];
		}

		$output = [];

		foreach ( $results as $set ) {
			$output[ (int) $set['site_id'] ] = (int) $set['content_id'];
		}

		wp_cache_set( $cache_key, $output, $this->cache_group );

		return $output;
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

		return $result[0];
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

		$existing = $this->get_relations( $source_site_id, $source_content_id, $type );

		if ( isset( $existing[ $target_site_id ] ) ) {
			if ( $existing[ $target_site_id ] === $target_content_id ) {
				return true;
			}

			$this->delete_relation(
				$translation_ids['ml_source_blogid'],
				$target_site_id,
				$translation_ids['ml_source_elementid'],
				0, // old content id
				$type
			);
		}

		$result = (bool) $this->insert_row(
			$translation_ids['ml_source_blogid'],
			$target_site_id,
			$translation_ids['ml_source_elementid'],
			$target_content_id,
			$type
		);

		$cache_key = $this->get_cache_key( $source_site_id, $source_content_id, $type );
		wp_cache_delete( $cache_key, $this->cache_group );

		\Inpsyde\MultilingualPress\debug(
			current_filter() . '/' . __METHOD__ . '/' . __LINE__ . " - {$this->db->last_query}"
		);

		return $result;
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

		$result = (int) $this->db->update(
			$this->table,
			[
				'ml_source_blogid'    => $relations[0]['ml_source_blogid'],
				'ml_source_elementid' => $relations[0]['ml_source_elementid'],
			],
			[
				'ml_source_blogid'    => $relations[1]['ml_source_blogid'],
				'ml_source_elementid' => $relations[1]['ml_source_elementid'],
				'ml_type'             => $type,
			],
			[
				'%d',
				'%d',
			],
			[
				'%d',
				'%d',
				'%s',
			]
		);

		\Inpsyde\MultilingualPress\debug(
			current_filter() . '/' . __METHOD__ . '/' . __LINE__ . " - {$this->db->last_query}"
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

		\Inpsyde\MultilingualPress\debug(
			current_filter() . '/' . __METHOD__ . '/' . __LINE__ . " - {$this->db->last_query}"
		);

		return [
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id,
		];
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

		$result = (int) $this->db->insert( $this->table, [
			'ml_source_blogid'    => $source_site_id,
			'ml_source_elementid' => $source_content_id,
			'ml_blogid'           => $target_site_id,
			'ml_elementid'        => $target_content_id,
			'ml_type'             => $type,
		] );

		\Inpsyde\MultilingualPress\debug(
			current_filter() . '/' . __METHOD__ . '/' . __LINE__ . " - {$this->db->last_query}"
		);

		return $result;
	}

	/**
	 * Checks if the site with the given ID has any relations of the given (or any) content type.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $site_id Site ID.
	 * @param string $type    Optional. Content type. Defaults to empty string.
	 *
	 * @return bool Whether or not the site with the given ID has any relations of the given (or any) content type.
	 */
	public function has_site_relations( $site_id, $type = '' ) {

		$args = [ $site_id ];

		$query = "SELECT ml_id FROM {$this->table} WHERE ml_blogid = %d AND ml_source_blogid != ml_blogid";
		if ( $type ) {
			$query .= ' AND ml_type = %s';

			$args[] = $type;
		}

		return (bool) $this->db->query( $this->db->prepare( "$query LIMIT 1", $args ) );
	}

	/**
	 * Copies all relations of the given (or any) content type from the given source site to the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * content IDs.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $source_site_id      Source site ID.
	 * @param int    $destination_site_id Destination site ID.
	 * @param string $type                Optional. Content type. Defaults to empty string.
	 *
	 * @return int The number of relations duplicated.
	 */
	public function duplicate_relations( $source_site_id, $destination_site_id, $type = '' ) {

		$args = [
			$destination_site_id,
			$source_site_id,
		];

		$query = "
INSERT INTO {$this->table} (
	ml_source_blogid,
	ml_source_elementid,
	ml_blogid,
	ml_elementid,
	ml_type
)
SELECT ml_source_blogid, ml_source_elementid, %d, ml_elementid, ml_type
FROM {$this->table}
WHERE ml_blogid = %d";
		if ( $type ) {
			$query .= ' AND ml_type = %s';

			$args[] = $type;
		}

		return (int) $this->db->query( $this->db->prepare( $query, $args ) );
	}

	/**
	 * Relates all posts between the given source site and the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * post IDs. Furthermore, the current site is assumed to be either the source site or the destination site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $source_site_id      Source site ID.
	 * @param int $destination_site_id Destination site ID.
	 *
	 * @return int The number of relations inserted.
	 */
	public function relate_all_posts( $source_site_id, $destination_site_id ) {

		$inserted = 0;

		// TODO: Restrict to "supported post types", and improve post status (e.g., `NOT IN ('trash', 'auto-draft')`?).
		$query = "
INSERT INTO {$this->table} (
	ml_source_blogid,
	ml_source_elementid,
	ml_blogid,
	ml_elementid,
	ml_type
)
SELECT %d, ID, %d, ID, 'post'
FROM {$this->db->posts}
WHERE post_status IN ('publish', 'future', 'draft', 'pending', 'private')";

		foreach ( [ $source_site_id, $destination_site_id ] as $site_id ) {
			$inserted += (int) $this->db->query( $this->db->prepare( $query, $source_site_id, $site_id ) );
		}

		return $inserted;
	}

	/**
	 * Relates all terms between the given source site and the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * term taxonomy IDs. Furthermore, the current site is assumed to be either the source site or the destination site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $source_site_id      Source site ID.
	 * @param int $destination_site_id Destination site ID.
	 *
	 * @return int The number of relations inserted.
	 */
	public function relate_all_terms( $source_site_id, $destination_site_id ) {

		$inserted = 0;

		$query = "
INSERT INTO {$this->table} (
	ml_source_blogid,
	ml_source_elementid,
	ml_blogid,
	ml_elementid,
	ml_type
)
SELECT %d, term_taxonomy_id, %d, term_taxonomy_id, 'term'
FROM {$this->db->term_taxonomy}";

		foreach ( [ $source_site_id, $destination_site_id ] as $site_id ) {
			$inserted += (int) $this->db->query( $this->db->prepare( $query, $source_site_id, $site_id ) );
		}

		return $inserted;
	}
}
