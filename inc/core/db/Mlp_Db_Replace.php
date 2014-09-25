<?php # -*- coding: utf-8 -*-
/**
 * Replace one string with another in multiple tables at once.
 *
 * Usage:
 * <pre><code>
 * $tables = array (
 *    $wpdb->posts         => array (
 *        'post_content',
 *        'post_excerpt',
 *        'post_content_filtered',
 *    ),
 *    $wpdb->term_taxonomy => array (
 *        'description'
 *    ),
 *    $wpdb->comments      => array (
 *        'comment_content'
 *    )
 *);
 * $db_replace    = new Mlp_Db_Replace( $tables, 'Foo', 'Bar' );
 * $affected_rows = $db_replace->replace();
 * </code></pre>
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2014.07.17
 */
class Mlp_Db_Replace {

	/**
	 * @var array
	 */
	private $tables;

	/**
	 * @var string
	 */
	private $from;

	/**
	 * @var string
	 */
	private $replacement;

	/**
	 * @type wpdb
	 */
	private $wpdb;

	/**
	 * Constructor
	 *
	 * @param  array  $tables Table names as keys, columns as value arrays
	 * @param  string $from   String to find, will be escaped.
	 * @param  string $replacement     String to use as replacement, will be escaped.
	 * @param  wpdb   $wpdb
	 */
	public function __construct( Array $tables, $from, $replacement, wpdb $wpdb ) {

		$this->tables      = $tables;
		$this->from        = $wpdb->_real_escape( $from );
		$this->replacement = $wpdb->_real_escape( $replacement );
		$this->wpdb = $wpdb;
	}

	/**
	 * Replace references to old URI with the new one.
	 *
	 * @return int|FALSE Number of affected rows or FALSE on error
	 */
	public function replace() {

		$sql = $this->get_replace_sql();
		return $this->wpdb->query( $sql );
	}

	/**
	 * Create an SQL query to replace the same strings in multiple tables and columns.
	 *
	 * @return string Complete SQL query
	 */
	public function get_replace_sql() {

		$table_names = join( '`,`', array_keys( $this->tables ) );
		$update      = "UPDATE `" . $table_names ."` SET \n";
		$replace     = array();

		foreach ( $this->tables as $table => $columns )
			$replace[] = $this->get_column_replace_sql( $table, $columns );

		return $update . join( ', ', $replace );
	}

	/**
	 * Create replacement SQL for single table with multiple columns.
	 *
	 * @param  string $table   Table name
	 * @param  array  $columns Column names
	 * @return string
	 */
	private function get_column_replace_sql( $table, Array $columns ) {

		$rows = array ();

		foreach ( $columns as $column )
			$rows[] = "$table.$column = REPLACE( $table.$column, '$this->from', '$this->replacement' )";

		return join( ",\n", $rows );
	}
}