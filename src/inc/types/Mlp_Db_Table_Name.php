<?php # -*- coding: utf-8 -*-
/**
 * Provide a table name.
 *
 * @version 2015.08.31
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Db_Table_Name implements Mlp_Db_Table_Name_Interface {

	/**
	 * @var string
	 */
	private $table_name;

	/**
	 * @var array
	 */
	private $all_table_names = array();

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param string                      $table_name Current table name.
	 * @param Mlp_Db_Table_List_Interface $table_list All WordPress table names for the current installation.
	 */
	public function __construct(
		$table_name,
		Mlp_Db_Table_List_Interface $table_list
	) {

		$this->table_name = $table_name;

		$this->all_table_names = $table_list->get_all_table_names();
	}

	/**
	 * Return the table name.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->table_name;
	}

	/**
	 * Check whether or not the table already exists.
	 *
	 * @return bool
	 */
	public function exists() {

		return in_array( $this->table_name, $this->all_table_names, true );
	}

	/**
	 * Check whether or not the current name is a valid table name.
	 *
	 * We allow just a limited set of characters for compatibility reasons. A quoted identifier may contain almost any
	 * character, but we expect only identifiers that can be used unquoted and in any SQL implementation.
	 *
	 * @return bool
	 */
	private function is_valid() {

		// too long
		if ( isset( $this->table_name[64] ) ) {
			return false;
		}

		// too short
		if ( ! isset( $this->table_name[0] ) ) {
			return false;
		}

		return (bool) ! preg_match(
			'|[^a-z0-9_]|i',
			$this->table_name
		);
	}

}
