<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

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
	 *
	 * @param wpdb $db WordPress database object.
	 */
	public function __construct( wpdb $db ) {

		$this->db = $db;
	}

	/**
	 * Replaces one string with another all given columns of the given table at once.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $table       The name of the table to replace the string in.
	 * @param string[] $columns     The names of all columns to replace the string in.
	 * @param string   $search      The string to replace.
	 * @param string   $replacement The replacement.
	 *
	 * @return int The number of affected rows.
	 */
	public function replace_string(
		string $table,
		array $columns,
		string $search,
		string $replacement
	): int {

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
	 * @param string   $replacement The replacement.
	 *
	 * @return string The SQL string for replacing the given string with the given replacement in the given columns.
	 */
	private function get_replacements_sql( array $columns, string $search, string $replacement ): string {

		$columns = preg_filter( '~^[a-zA-Z_][a-zA-Z0-9_]*$~', '$0', $columns );

		$replacements_sql = array_reduce( $columns, function ( $sql, $column ) use ( $search, $replacement ) {

			return $this->db->prepare( "$sql\n\t$column = REPLACE ($column,%s,%s),", $search, $replacement );
		}, '' );

		return substr( $replacements_sql, 0, -1 );
	}
}
