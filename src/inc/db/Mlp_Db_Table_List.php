<?php
/**
 * class Mlp_Db_Table_List
 *
 * Get table names for various contexts from either WordPress or the
 * INFORMATION SCHEMA tables.
 *
 * @version 2014.08.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Db_Table_List implements Mlp_Db_Table_List_Interface {

	/**
	 * @type wpdb
	 */
	private $wpdb;

	/**
	 * @type string
	 */
	private $no_tables_found = 'no_tables_found';

	/**
	 * Constructor.
	 *
	 * @param wpdb $wpdb
	 */
	public function __construct( wpdb $wpdb ) {

		$this->wpdb = $wpdb;

		if ( ! function_exists( 'wp_get_db_schema' ) )
			require_once ABSPATH . 'wp-admin/includes/schema.php';
	}

	/**
	 * Get all table names for the current installation
	 *
	 * @api
	 * @return array
	 */
	public function get_all_table_names() {

		$names = $this->get_names_from_db();

		if ( array ( $this->no_tables_found ) === $names )
			return array();

		return $names;
	}

	/**
	 * Get standard network tables
	 *
	 * @api
	 * @return array
	 */
	public function get_network_core_tables() {

		$all_tables     = $this->get_all_table_names();
		$network_tables = $this->get_network_core_table_names();

		return array_intersect( $all_tables, $network_tables );
	}

	/**
	 * Get standard site tables
	 *
	 * @api
	 * @param  int   $site_id
	 * @return array
	 */
	public function get_site_core_tables( $site_id ) {

		$schema = wp_get_db_schema( 'blog', $site_id );
		$prefix = $this->wpdb->get_blog_prefix( $site_id );

		return $this->extract_names_from_schema( $schema, $prefix );
	}

	/**
	 * Get custom site tables
	 *
	 * Might return custom network tables from other plugins if the site id is
	 * the network id.
	 *
	 * @api
	 * @param  int   $site_id
	 * @return array
	 */
	public function get_site_custom_tables( $site_id ) {

		$all_tables = $this->get_all_table_names();
		$prefix     = $this->wpdb->get_blog_prefix( $site_id );
		$exclude    = $this->get_not_custom_site_tables( $site_id, $prefix );
		$out        = array ();

		foreach ( $all_tables as $name ) {

			if ( $this->is_valid_custom_site_table( $name, $exclude, $prefix ) )
				$out[] = $name;
		}

		return $out;
	}

	/**
	 * Get this plugin's table names
	 *
	 * @api
	 * @return array
	 */
	public function get_mlp_tables() {

		return array (
			$this->wpdb->base_prefix . 'mlp_languages',
			$this->wpdb->base_prefix . 'mlp_site_relations',
			$this->wpdb->base_prefix . 'multilingual_linked'
		);
	}

	/**
	 * Check whether a table name can be a custom table for a site
	 *
	 * @param  string $name        Table name to check
	 * @param  array  $exclude     List of invalid names
	 * @param  string $prefix Database prefix for the current site
	 * @return bool
	 */
	private function is_valid_custom_site_table( $name, $exclude, $prefix ) {

		if ( in_array( $name, $exclude, true ) ) {
			return false;
		}

		return (bool) preg_match( '~^' . $prefix . '[^0-9]~', $name );
	}

	/**
	 * Get all table names that are not custom tables for a site
	 *
	 * @param  int    $site_id
	 * @param  string $site_prefix
	 * @return array
	 */
	private function get_not_custom_site_tables( $site_id, $site_prefix ) {

		$core    = $this->get_site_core_tables( $site_id );

		if ( $site_prefix !== $this->wpdb->base_prefix )
			return $core;

		// We are on the main site. This is difficult, because there is no clear
		// distinction between custom network tables and custom site tables.
		$network = $this->get_network_core_table_names();
		$mlp     = $this->get_mlp_tables();

		return array_merge( $core, $network, $mlp );
	}

	/**
	 * Get standard network tables
	 *
	 * @return array
	 */
	private function get_network_core_table_names() {

		$schema = wp_get_db_schema( 'global' );

		return $this->extract_names_from_schema( $schema, $this->wpdb->base_prefix );
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
		$pattern = '~CREATE TABLE (' . $prefix . '.*) \(~';

		preg_match_all( $pattern, $schema, $matches );

		if ( empty ( $matches[ 1 ] ) )
			return array();

		return $matches[ 1 ];
	}

	/**
	 * Fetch all table names for this installation.
	 *
	 * @return array
	 */
	private function get_names_from_db() {

		$query = $this->get_sql_for_all_tables( $this->wpdb->base_prefix );
		$names = $this->wpdb->get_col( $query );

		// Make sure there is something in the array, so we don't try that again.
		if ( empty ( $names ) )
			return array ( $this->no_tables_found );

		return $names;
	}

	/**
	 * Get SQL to fetch the table names.
	 *
	 * @param  string $prefix
	 * @return string
	 */
	private function get_sql_for_all_tables( $prefix ) {

		$like = $this->wpdb->esc_like( $prefix );

		return "SHOW TABLES LIKE '$like%'";
	}
}
