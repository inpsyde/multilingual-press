<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\Table\SiteRelationsTable;

/**
 * Site relations API implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class WPDBSiteRelations implements SiteRelations {

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $table;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb $db    WordPress database object.
	 * @param Table $table Site relations table object.
	 */
	public function __construct( \wpdb $db, Table $table ) {

		$this->db = $db;

		$this->table = $table->name();
	}

	/**
	 * Deletes the relationship between the given sites. If only one site is given, all its relations will be deleted.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Optional. Another site ID. Defaults to 0.
	 *
	 * @return int The number of rows affected.
	 */
	public function delete_relation( int $site_1, int $site_2 = 0 ): int {

		$query = "DELETE FROM {$this->table}";

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		if ( ! $site_2 ) {
			$query .= sprintf(
				' WHERE %1$s = %%d OR %2$s = %%d',
				SiteRelationsTable::COLUMN_SITE_1,
				SiteRelationsTable::COLUMN_SITE_2
			);

			$args = [ $site_1, $site_1 ];
		} else {
			$query .= sprintf(
				' WHERE (%1$s = %%d AND %2$s = %%d) OR (%1$s = %%d AND %2$s = %%d)',
				SiteRelationsTable::COLUMN_SITE_1,
				SiteRelationsTable::COLUMN_SITE_2
			);

			$args = [ $site_1, $site_2, $site_2, $site_1 ];
		}

		$query = $this->db->prepare( $query, $args );

		return (int) $this->db->query( $query );
	}

	/**
	 * Returns an array with site IDs as keys and arrays with the IDs of all related sites as values.
	 *
	 * @since 3.0.0
	 *
	 * @return int[] The array with site IDs as keys and arrays with the IDs of all related sites as values.
	 */
	public function get_all_relations(): array {

		$query = sprintf(
			'SELECT %2$s, %3$s FROM %1$s ORDER BY %2$s ASC, %3$s ASC',
			$this->table,
			SiteRelationsTable::COLUMN_SITE_1,
			SiteRelationsTable::COLUMN_SITE_2
		);

		$rows = $this->db->get_results( $query, ARRAY_A );

		return $rows
			? $this->get_site_relations_from_query_results( $rows )
			: [];
	}

	/**
	 * Returns an array holding the IDs of all sites related to the site with the given (or current) ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int  $site_id      Optional. Site ID. Defaults to 0.
	 * @param bool $include_site Optional. Whether or not to include the given site ID. Defaults to false.
	 *
	 * @return int[] The array holding the IDs of all sites related to the site with the given (or current) ID.
	 */
	public function get_related_site_ids( int $site_id = 0, bool $include_site = false ): array {

		$site_id = $site_id ?: get_current_blog_id();
		if ( ! absint( $site_id ) ) {
			return [];
		}

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
(
	SELECT DISTINCT %2$s as site_id
	FROM %1$s
	WHERE %3$s = %%d
)
UNION
(
	SELECT DISTINCT %3$s
	FROM %1$s
	WHERE %2$s = %%d
)
ORDER BY site_id ASC',
			$this->table,
			SiteRelationsTable::COLUMN_SITE_1,
			SiteRelationsTable::COLUMN_SITE_2
		);
		$query = $this->db->prepare( $query, $site_id, $site_id );

		$rows = $this->db->get_col( $query );
		if ( ! $rows ) {
			return [];
		}

		if ( $include_site ) {
			$rows[] = (int) $site_id;
		}

		return array_map( 'intval', $rows );
	}

	/**
	 * Creates relations between one site and one or more other sites.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $base_site_id Base site ID.
	 * @param int[] $site_ids     An array of site IDs.
	 *
	 * @return int The number of rows affected.
	 */
	public function insert_relations( int $base_site_id, array $site_ids ): int {

		// We don't want to relate a site with itself.
		$site_ids = array_diff( $site_ids, [ $base_site_id ] );
		if ( ! $site_ids ) {
			return 0;
		}

		$values = array_map( function ( $site_id ) use ( $base_site_id ) {

			return $this->get_value_pair( $base_site_id, (int) $site_id );
		}, $site_ids );
		if ( ! $values ) {
			return 0;
		}

		$query = sprintf(
			'INSERT IGNORE INTO %1$s (%2$s, %3$s) VALUES (%4$s)',
			$this->table,
			SiteRelationsTable::COLUMN_SITE_1,
			SiteRelationsTable::COLUMN_SITE_2,
			implode( ',', $values )
		);

		return (int) $this->db->query( $query );
	}

	/**
	 * Sets the relations for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $base_site_id Base site ID.
	 * @param int[] $site_ids     Site IDs.
	 *
	 * @return int The number of rows affected.
	 */
	public function set_relationships( int $base_site_id, array $site_ids ): int {

		$related_site_ids = $this->get_related_site_ids( $base_site_id );
		if ( $related_site_ids === $site_ids ) {
			return 0;
		}

		if ( ! $site_ids ) {
			return $this->delete_relation( $base_site_id );
		}

		$to_delete = array_diff( $related_site_ids, $site_ids );

		$changed = array_reduce( $to_delete, function ( $changed, $site_id ) use ( $base_site_id ) {

			return $changed + $this->delete_relation( $base_site_id, $site_id );
		}, 0 );

		$to_insert = $to_delete ? array_diff( $site_ids, $to_delete ) : $site_ids;

		$changed += $this->insert_relations( $base_site_id, $to_insert );

		return $changed;
	}

	/**
	 * Returns a (value1, value2) syntax string according to the given site IDs.
	 *
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Site ID.
	 *
	 * @return string The (value1, value2) syntax string according to the given site IDs.
	 */
	private function get_value_pair( int $site_1, int $site_2 ): string {

		// Swap values to make sure the lower value is the first.
		if ( $site_1 > $site_2 ) {
			list( $site_1, $site_2 ) = [ $site_2, $site_1 ];
		}

		return "($site_1, $site_2)";
	}

	/**
	 * Returns a formatted array with site relations included in the given query results.
	 *
	 * @param string[] $rows Query results.
	 *
	 * @return int[] The formatted array with the given site relations data.
	 */
	private function get_site_relations_from_query_results( array $rows ): array {

		$relations = array_reduce( $rows, function ( array $relations, array $row ) {

			$site_1 = (int) $row[ SiteRelationsTable::COLUMN_SITE_1 ];

			$site_2 = (int) $row[ SiteRelationsTable::COLUMN_SITE_2 ];

			$relations[ $site_1 ][ $site_2 ] = $site_2;

			$relations[ $site_2 ][ $site_1 ] = $site_1;

			return $relations;
		}, [] );

		return array_map( 'array_values', $relations );
	}
}
