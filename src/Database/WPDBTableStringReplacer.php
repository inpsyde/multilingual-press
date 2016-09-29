<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

use wpdb;

/**
 * Table string replacer implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
final class WPDBTableStringReplacer implements TableStringReplacer {

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
	 * Replaces one string with another all given columns of the given table at once.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $table       The name of the table to replace the string in.
	 * @param string[] $columns     The names of all columns to replace the string in.
	 * @param string   $search      The string to replace.
	 * @param string   $replacement The replacment.
	 *
	 * @return int The number of affected rows.
	 */
	public function replace_string(
		$table,
		array $columns,
		$search,
		$replacement
	) {

		if ( preg_match( '|[^a-z0-9_]|i', $table ) ) {
			return 0;
		}

		$replacements = $this->get_replacements_sql( $columns, $search, $replacement );
		if ( ! $replacements ) {
			return 0;
		}

		$this->db->query( 'SET autocommit = 0' );

		$affected_rows = (int) $this->db->query( "UPDATE $table SET $replacements" );

		$this->db->query( 'COMMIT' );

		$this->db->query( 'SET autocommit = 1' );

		return $affected_rows;
	}

	/**
	 * Returns the according SQL string for replacing the given string with the given replacement in the given columns.
	 *
	 * @param string[] $columns     The names of all columns to replace the string in.
	 * @param string   $search      The string to replace.
	 * @param string   $replacement The replacment.
	 *
	 * @return string The SQL string for replacing the given string with the given replacement in the given columns.
	 */
	private function get_replacements_sql( array $columns, $search, $replacement ) {

		$columns = array_filter( $columns, function ( $column ) {

			return (bool) preg_match( '~^[a-zA-Z_][a-zA-Z0-9_]*$~', $column );
		} );

		$replacements_sql = array_reduce( $columns, function ( $sql, $column ) use ( $search, $replacement ) {

			return $this->db->prepare( "$sql\n\t$column = REPLACE ($column,%s,%s),", $search, $replacement );
		}, '' );

		return substr( $replacements_sql, 0, -1 );
	}
}
