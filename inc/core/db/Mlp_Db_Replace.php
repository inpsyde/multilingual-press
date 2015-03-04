<?php # -*- coding: utf-8 -*-
/**
 * Replace one string with another in multiple columns at once.
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2015.01.13
 */
class Mlp_Db_Replace {

	/**
	 * @type wpdb
	 */
	private $wpdb;

	/**
	 * Column names that did not pass our validation
	 *
	 * @type array
	 */
	private $invalid_columns = array();

	/**
	 * Constructor
	 *
	 * @param  wpdb   $wpdb
	 */
	public function __construct( wpdb $wpdb ) {

		$this->wpdb = $wpdb;
	}

	/**
	 * Replace string in multiple columns in a table
	 *
	 * @param  Mlp_Db_Table_Name_Interface $table
	 * @param  array  $columns
	 * @param  string $search
	 * @param  string $replacement
	 * @return int    Number of affected rows
	 */
	public function replace_string(
		Mlp_Db_Table_Name_Interface $table,
		Array                       $columns,
		                            $search,
		                            $replacement
	) {

		$name         = $table->get_name();
		$replacements = $this->get_replacement_sql( $columns, $search, $replacement );

		if ( empty ( $replacements ) )
			return 0;

		$this->wpdb->query( 'SET autocommit = 0;' );
		$num = (int) $this->wpdb->query( "UPDATE `$name` SET $replacements" );
		$this->wpdb->query( 'COMMIT;' );
		$this->wpdb->query( 'SET autocommit = 1;' );

		return $num;
	}

	/**
	 * Get the columns that did not pass our validation
	 *
	 * This is mainly for debugging.
	 *
	 * @return array
	 */
	public function get_invalid_columns() {

		return $this->invalid_columns;
	}

	/**
	 * Get the SQL for the whole table
	 *
	 * @param  array  $columns
	 * @param  string $search
	 * @param  string $replacement
	 * @return string
	 */
	private function get_replacement_sql( Array $columns, $search, $replacement ) {

		$rows = array ();

		foreach ( $columns as $column ) {
			if ( ! $this->is_valid_column_name( $column ) ) {
				$this->invalid_columns[] = $column;
				continue;
			}

			$sql = $this->get_column_sql( $column, $search, $replacement );

			if ( ! empty ( $sql ) )
				$rows[] = $sql;
		}

		return join( ",\n", $rows );
	}

	/**
	 * Get the SQL for one column
	 *
	 * @param  string $column
	 * @param  string $search
	 * @param  string $replacement
	 * @return string
	 */
	private function get_column_sql( $column, $search, $replacement ) {

		$sql = "$column = REPLACE( $column, %s, %s )";
		// wpdb::prepare() returns null, false or a string. useless.
		return (string) $this->wpdb->prepare( $sql, $search, $replacement );
	}

	/**
	 * Validate the column name
	 *
	 * @param  string $column
	 * @return bool
	 */
	private function is_valid_column_name( $column ) {

		return (bool) preg_match( '~^[a-zA-Z_][a-zA-Z0-9_]*$~', $column );
	}
}