<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

use wpdb;

/**
 * Table list implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
final class WPDBTableList implements TableList {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->db = $GLOBALS['wpdb'];

		/**
		 * WordPress file with the wp_get_db_schema() function.
		 */
		require_once ABSPATH . 'wp-admin/includes/schema.php';
	}

	/**
	 * Returns an array with the names of all tables.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The names of all tables.
	 */
	public function all_tables() {

		$cache_key = 'all_tables';

		$cache_group = 'multilingualpress';

		$all_tables = wp_cache_get( $cache_key, $cache_group );
		if ( is_array( $all_tables ) ) {
			return $all_tables;
		}

		$query = $this->db->prepare( "SHOW TABLES LIKE '%s'", $this->db->esc_like( "{$this->db->base_prefix}%" ) );

		$all_tables = $this->db->get_col( $query );;

		wp_cache_set( $cache_key, $all_tables, $cache_group );

		return $all_tables;
	}

	/**
	 * Returns an array with the names of all network tables.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The names of all network tables.
	 */
	public function network_tables() {

		$all_tables = $this->all_tables();

		$network_tables = $this->extract_tables_from_schema( wp_get_db_schema( 'global' ), $this->db->base_prefix );

		return array_intersect( $all_tables, $network_tables );
	}

	/**
	 * Returns an array with the names of all tables for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string[] The names of all tables for the site with the given ID.
	 */
	public function site_tables( $site_id ) {

		$prefix = $this->db->get_blog_prefix( $site_id );

		return $this->extract_tables_from_schema( wp_get_db_schema( 'blog', $site_id ), $prefix );
	}

	/**
	 * Extracts all table names (including the given prefix) from the given schema.
	 *
	 * @param string $schema Schema string.
	 * @param string $prefix Optional. Table prefix. Defaults to empty string.
	 *
	 * @return string[] The table names included in the given schema.
	 */
	private function extract_tables_from_schema( $schema, $prefix = '' ) {

		preg_match_all( '~CREATE TABLE (' . $prefix . '.*) \(~', $schema, $matches );

		return empty( $matches[1] ) ? [] : $matches[1];
	}
}
