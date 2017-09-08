<?php
/**
 * class Mlp_Table_Names
 *
 * Get table names for various contexts from either WordPress or the
 * INFORMATION SCHEMA tables.
 *
 * @version 2014.08.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Table_Names implements Mlp_Table_Names_Interface {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var string
	 */
	private $cache_group = 'mlp-table-names';

	/**
	 * Constructor.
	 *
	 * @param wpdb $wpdb
	 * @param  int    $site_id Optional, defaults to the current site.
	 */
	public function __construct( wpdb $wpdb, $site_id = 0 ) {

		$this->wpdb    = $wpdb;
		$this->site_id = $this->prepare_site_id( $site_id );

		if ( ! function_exists( 'wp_get_db_schema' ) ) {
			require_once ABSPATH . 'wp-admin/includes/schema.php';
		}
	}

	/**
	 * Tables for the network only, not site specific, not custom.
	 *
	 * @return array
	 */
	public function get_core_network_tables() {

		$cache_key = 'network-core';
		$cache     = wp_cache_get( $cache_key, $this->cache_group );

		if ( $cache ) {
			return $cache;
		}

		$schema = wp_get_db_schema( 'global' );
		$tables = $this->extract_names_from_schema( $schema, $this->wpdb->base_prefix );

		wp_cache_set( $cache_key, $tables, $this->cache_group );

		return $tables;
	}

	/**
	 * Get all tables for a site, core and custom.
	 *
	 * If we are on the main site of a network, we try to exclude the
	 * core network tables. There is no way to exclude *custom* network tables,
	 * so this is not perfect.
	 *
	 * @return array
	 */
	public function get_all_site_tables() {

		$cache_key = "site-all-{$this->site_id}";
		$cache     = wp_cache_get( $cache_key, $this->cache_group );
		$exclude   = array();

		if ( $cache ) {
			return $cache;
		}

		if ( $this->wpdb->base_prefix === $this->wpdb->prefix ) {
			$exclude = $this->get_core_network_tables();
		}

		$tables = $this->query_information_schema( $exclude );

		wp_cache_set( $cache_key, $tables, $this->cache_group );

		return $tables;
	}

	/**
	 * Get core tables only.
	 *
	 * @param  bool $do_prefix Should the table names be prefixed?
	 * @return array
	 */
	public function get_core_site_tables( $do_prefix = true ) {

		$cache_key = "site-core-{$this->site_id}-"
			. ( $do_prefix ? 1 : 0 );
		$cache     = wp_cache_get( $cache_key, $this->cache_group );

		if ( $cache ) {
			return $cache;
		}

		if ( get_current_blog_id() !== $this->site_id ) {
			switch_to_blog( $this->site_id );
		}

		$schema = wp_get_db_schema( 'blog', $this->site_id );
		$tables = $this->extract_names_from_schema( $schema, $this->wpdb->prefix );

		if ( $do_prefix ) {
			$tables = $this->prefix_table_names( $tables, $this->wpdb->prefix );
		}

		restore_current_blog();

		wp_cache_set( $cache_key, $tables, $this->cache_group );

		return $tables;
	}

	/**
	 * Get custom tables for a site.
	 *
	 * @return array
	 */
	public function get_custom_site_tables() {

		$cache_key = "site-custom-{$this->site_id}";
		$cache     = wp_cache_get( $cache_key, $this->cache_group );

		if ( $cache ) {
			return $cache;
		}

		$exclude = $this->get_core_site_tables();
		$tables  = $this->query_information_schema( $exclude );

		if ( $this->wpdb->base_prefix === $this->wpdb->prefix ) {
			$tables = array_diff( $tables, $this->get_core_network_tables() );
		}

		wp_cache_set( $cache_key, $tables, $this->cache_group );

		return $tables;
	}

	/**
	 * Make sure, there is always a valid site ID.
	 *
	 * @param  int $site_id
	 * @return int
	 */
	private function prepare_site_id( $site_id ) {

		if ( 0 === $site_id ) {
			return get_current_blog_id();
		}

		return $site_id;
	}

	/**
	 * @param array $exclude
	 * @return array
	 */
	private function query_information_schema( array $exclude = array() ) {

		$sql = $this->get_information_schema_sql( $exclude );

		return $this->wpdb->get_col( $sql );
	}

	/**
	 * There is no API for the names of the core tables, so we read the SQL for
	 * the CREATE statements and extract the names per regex.
	 *
	 * @param  string $schema
	 * @param  string $prefix
	 * @return array
	 */
	private function extract_names_from_schema( $schema, $prefix = '' ) {

		$matches = array();
		$pattern = '~CREATE TABLE ' . $prefix . '(.*) \(~';

		preg_match_all( $pattern, $schema, $matches );

		if ( empty( $matches[1] ) ) {
			return array();
		}

		return $matches[1];
	}

	/**
	 * @param array $tables
	 * @return string
	 */
	private function get_exclude_sql( array $tables ) {

		$joined = join( "','", $tables );

		return " AND TABLE_NAME NOT IN ('$joined')";
	}

	/**
	 * Make sure all table names use the db prefix.
	 *
	 * @param  array  $tables
	 * @param  string $prefix
	 * @return array
	 */
	private function prefix_table_names( array $tables, $prefix ) {

		foreach ( $tables as $key => $name ) {
			$tables[ $key ] = $prefix . $name;
		}

		return $tables;
	}

	/**
	 * @param array $exclude
	 * @return string
	 */
	private function get_information_schema_sql( array $exclude = array() ) {
		$sql = "
			SELECT TABLE_NAME
			FROM information_schema.tables
			WHERE TABLE_NAME REGEXP '{$this->wpdb->prefix}[^0-9]'";

		if ( ! empty( $exclude ) ) {
			$sql .= $this->get_exclude_sql( $exclude );
		}

		return $sql;
	}
}
