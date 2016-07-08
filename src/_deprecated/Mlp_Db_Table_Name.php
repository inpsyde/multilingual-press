<?php # -*- coding: utf-8 -*-

_deprecated_file(
	'Mlp_Db_Table_Name',
	'3.0.0'
);

/**
 * @deprecated 3.0.0 Deprecated with no alternative available.
 */
class Mlp_Db_Table_Name implements Mlp_Db_Table_Name_Interface {

	/**
	 * @var string[]
	 */
	private $all_table_names = [ ];

	/**
	 * @var string
	 */
	private $table_name;

	/**
	 * @deprecated 3.0.0 Deprecated with no alternative available.
	 *
	 * @param string                      $table_name
	 * @param Mlp_Db_Table_List_Interface $table_list
	 */
	public function __construct( $table_name, Mlp_Db_Table_List_Interface $table_list ) {

		$this->table_name = (string) $table_name;

		if ( ! $this->is_valid() ) {
			trigger_error( 'Invalid table name: ' . esc_html( $table_name ), E_USER_ERROR );
		}

		$this->all_table_names = $table_list->get_all_table_names();
	}

	/**
	 * @deprecated 3.0.0 Deprecated with no alternative available.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->table_name;
	}

	/**
	 * @deprecated 3.0.0 Deprecated with no alternative available.
	 *
	 * @return bool
	 */
	public function exists() {

		return in_array( $this->table_name, $this->all_table_names, true );
	}

	/**
	 * @return bool
	 */
	private function is_valid() {

		if ( isset( $this->table_name[64] ) ) {
			return false;
		}

		if ( ! isset( $this->table_name[0] ) ) {
			return false;
		}

		return ! preg_match( '|[^a-z0-9_]|i', $this->table_name );
	}
}
