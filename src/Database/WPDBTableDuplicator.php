<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Database;

/**
 * Table duplicator implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
final class WPDBTableDuplicator implements TableDuplicator {

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb $db WordPress database object.
	 */
	public function __construct( \wpdb $db ) {

		$this->db = $db;
	}

	/**
	 * Creates a new table that is an exact duplicate of an existing table.
	 *
	 * @since 3.0.0
	 *
	 * @param string $existing_table Name of the existing table.
	 * @param string $new_table      Name of the new table.
	 *
	 * @return bool Whether or not the table was duplicated successfully.
	 */
	public function duplicate_table( string $existing_table, string $new_table ): bool {

		return (bool) $this->db->query( "CREATE TABLE IF NOT EXISTS {$new_table} LIKE {$existing_table}" );
	}
}
