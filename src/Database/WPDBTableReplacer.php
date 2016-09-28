<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

use wpdb;

/**
 * Table replacer implementations using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
final class WPDBTableReplacer implements TableReplacer {

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
	}

	/**
	 * Replaces the content of one table with another table's content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $destination  Name of the destination table.
	 * @param string $source       Name of the source table.
	 *
	 * @return bool Whether or not the table was replaced successfully.
	 */
	public function replace_table( $destination, $source ) {

		$has_primary_key = (bool) $this->db->get_results( "SHOW KEYS FROM $destination WHERE Key_name = 'PRIMARY'" );
		if ( $has_primary_key ) {
			$this->db->query( "ALTER TABLE $destination DISABLE KEYS" );
		}

		$this->db->query( "TRUNCATE TABLE $destination" );

		$replaced_table = (bool) $this->db->query( "INSERT INTO $destination SELECT * FROM $source" );

		if ( $has_primary_key ) {
			$this->db->query( "ALTER TABLE $destination ENABLE KEYS" );
		}

		return $replaced_table;
	}
}
