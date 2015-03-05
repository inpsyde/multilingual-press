<?php
/**
 * Provide a validated table name
 *
 * @version 2015.01.08
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

class Mlp_Db_Table_Name implements Mlp_Db_Table_Name_Interface {

	/**
	 * Current table name
	 *
	 * @type string
	 */
	private $table_name;

	/**
	 * All WordPress table names for the current installation.
	 *
	 * @type array
	 */
	private $all_table_names = array();

	/**
	 * @param string                      $table_name
	 * @param Mlp_Db_Table_List_Interface $table_list
	 */
	public function __construct(
		                            $table_name,
		Mlp_Db_Table_List_Interface $table_list
	) {

		$this->table_name = $table_name;

		if ( ! $this->is_valid() ) {
			trigger_error(
				'Invalid table name: ' . esc_html( $table_name ),
				E_USER_ERROR
			);
		}

		$this->all_table_names = $table_list->get_all_table_names();
	}

	/**
	 * Get the table name as string.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->table_name;
	}

	/**
	 * Does the table exists already?
	 *
	 * @return bool
	 */
	public function exists() {

		return in_array( $this->table_name, $this->all_table_names );
	}

	/**
	 * Whether the current name is a valid table name
	 *
	 * We allow just a limited set of characters for compatibility reasons.
	 * A quoted identifier may contain almost any character, but we expect
	 * only identifiers that can be used unquoted and in any SQL implementation.
	 *
	 * @return bool
	 */
	private function is_valid() {

		// too long
		if ( isset ( $this->table_name[64] ) )
			return FALSE;

		// too short
		if ( ! isset ( $this->table_name[0] ) )
			return FALSE;

		return (bool) preg_match(
			'~^[a-zA-Z_][a-zA-Z0-9_]*$~',
			$this->table_name
		);
	}
}