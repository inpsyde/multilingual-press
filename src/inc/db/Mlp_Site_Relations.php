<?php
/**
 * Handle relationships between sites (blogs) in a network.
 *
 * @version 2014.07.13
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Site_Relations implements Mlp_Site_Relations_Interface {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var string
	 */
	private $link_table_name;

	/**
	 * Internal cache for related sites.
	 *
	 * @var array
	 */
	private $related_sites = array();

	/**
	 * Constructor
	 *
	 * @param wpdb   $wpdb
	 * @param string $link_table_name
	 */
	public function __construct( wpdb $wpdb, $link_table_name ) {

		$this->wpdb            = $wpdb;
		$this->link_table_name = $wpdb->base_prefix . $link_table_name;
	}

	/**
	 * Fetch related sites.
	 *
	 * @param  int  $site_id
	 * @return array
	 */
	public function get_related_sites( $site_id = 0 ) {

		$site_id = $this->empty_site_id_fallback( $site_id );

		if ( isset( $this->related_sites[ $site_id ] ) ) {
			return $this->related_sites[ $site_id ];
		}

		$sql = $this->get_related_sites_sql( $site_id );

		$this->related_sites[ $site_id ] = array_map( 'intval', $this->wpdb->get_col( $sql ) );

		return $this->related_sites[ $site_id ];
	}

	/**
	 * Create new relation for one site with one or more others.
	 *
	 * @param int $site_1
	 * @param int|array $sites ID or array of IDs
	 * @return int Number of affected rows.
	 */
	public function set_relation( $site_1, $sites ) {
		$sites  = (array) $sites;
		$values = array();

		foreach ( $sites as $site_id ) {
			if ( $site_1 !== $site_id ) {
				$values[] = $this->get_value_pair( $site_1, $site_id );
			}
		}

		if ( empty( $values ) ) {
			return 0;
		}

		$sql    = 'INSERT IGNORE INTO `' . $this->link_table_name
			. '` ( `site_1`, `site_2` ) VALUES '
			. join( ', ', $values );

		return (int) $this->wpdb->query( $sql );
	}

	/**
	 * Delete relationships.
	 *
	 * @param int $site_1
	 * @param int $site_2 Optional. If left out, all relations will be deleted.
	 * @return int
	 */
	public function delete_relation( $site_1, $site_2 = 0 ) {

		$site_1 = (int) $site_1;
		$site_2 = (int) $site_2;
		$sql    = "DELETE FROM {$this->link_table_name} WHERE (`site_1` = $site_1 OR `site_2` = $site_1)";

		if ( 0 < $site_2 ) {
			$sql .= " AND (`site_1` = $site_2 OR `site_2` = $site_2)";
		}

		return (int) $this->wpdb->query( $sql );
	}

	/**
	 * Create SQL to fetch related sites.
	 *
	 * @param  int $site_id
	 * @return string
	 */
	private function get_related_sites_sql( $site_id ) {

		$sql = 'SELECT DISTINCT IF (site_1 = %1$d, site_2, site_1) as blog_id
			FROM ' . $this->link_table_name . '
			WHERE (site_1 = %1$d OR site_2 = %1$d)';

		return sprintf( $sql, $site_id );
	}

	/**
	 * Generate (val1, val2) syntax string.
	 *
	 * @param  int $site_1
	 * @param  int $site_2
	 * @return string
	 */
	private function get_value_pair( $site_1, $site_2 ) {

		// Swap values to make sure the lower value is the first.
		if ( $site_1 > $site_2 ) {
			list ( $site_1, $site_2 ) = array( $site_2, $site_1 );
		}

		return '(' . (int) $site_1 . ', ' . (int) $site_2 . ')';
	}

	/**
	 * Convert an empty site ID to the current site (blog) ID.
	 *
	 * @param  int $site_id
	 * @return int
	 */
	private function empty_site_id_fallback( $site_id ) {

		if ( 0 === (int) $site_id ) {
			$site_id = get_current_blog_id();
		}

		return $site_id;
	}
}
