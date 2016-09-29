<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Database\Table;
use wpdb;

/**
 * Site relations API implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class WPDBSiteRelations implements SiteRelations {

	/**
	 * @var wpdb
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
	 * @param Table $table Site relations table object.
	 */
	public function __construct( Table $table ) {

		$this->table = $table->name();

		$this->db = $GLOBALS['wpdb'];
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
	public function delete_relation( $site_1, $site_2 = 0 ) {

		$query = "DELETE FROM {$this->table}";

		if ( ! $site_2 ) {
			$query .= " WHERE site_1 = %d OR site_2 = %d";
			$args = [ $site_1, $site_1 ];
		} else {
			$query .= " WHERE (site_1 = %d AND site_2 = %d) OR (site_1 = %d AND site_2 = %d)";
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
	public function get_all_relations() {

		$query = "SELECT site_1, site_2 FROM {$this->table} ORDER BY site_1 ASC, site_2 ASC";

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
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return int[] The array holding the IDs of all sites related to the site with the given (or current) ID.
	 */
	public function get_related_site_ids( $site_id = 0 ) {

		$site_id = $site_id ?: get_current_blog_id();
		if ( ! absint( $site_id ) ) {
			return [];
		}

		$query = "
(
	SELECT DISTINCT site_1 as site_id
	FROM {$this->table}
	WHERE site_2 = %d
)
UNION
(
	SELECT DISTINCT site_2
	FROM {$this->table}
	WHERE site_1 = %d
)
ORDER BY site_id ASC";
		$query = $this->db->prepare( $query, $site_id, $site_id );

		$rows = $this->db->get_col( $query );

		return $rows
			? array_map( 'intval', $rows )
			: [];
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
	public function insert_relations( $base_site_id, array $site_ids ) {

		// We don't want to relate a site with itself.
		$site_ids = array_diff( $site_ids, [ $base_site_id ] );
		if ( ! $site_ids ) {
			return 0;
		}

		$values = array_map( function ( $site_id ) use ( $base_site_id ) {

			return $this->get_value_pair( $base_site_id, $site_id );
		}, $site_ids );
		if ( ! $values ) {
			return 0;
		}

		$values = join( ',', $values );

		return (int) $this->db->query( "INSERT IGNORE INTO {$this->table} (site_1, site_2) VALUES $values" );
	}

	/**
	 * Returns a (value1, value2) syntax string according to the given site IDs.
	 *
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Site ID.
	 *
	 * @return string The (value1, value2) syntax string according to the given site IDs.
	 */
	private function get_value_pair( $site_1, $site_2 ) {

		$site_1 = (int) $site_1;

		$site_2 = (int) $site_2;

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
	private function get_site_relations_from_query_results( array $rows ) {

		$relations = array_reduce( $rows, function ( array $relations, array $row ) {

			$site_1 = (int) $row['site_1'];

			$site_2 = (int) $row['site_2'];

			$relations[ $site_1 ][ $site_2 ] = $site_2;

			$relations[ $site_2 ][ $site_1 ] = $site_1;

			return $relations;
		}, [] );

		return array_map( 'array_values', $relations );
	}
}
