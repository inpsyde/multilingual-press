<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Db_Access
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Db_Access implements Mlp_Data_Access {

	/**
	 * @var string
	 */
	private $table_name;

	/**
	 * @param     $table_name
	 */
	public function __construct( $table_name ) {

		$this->table_name = (string) $table_name;
	}

	/**
	 * TODO: Get rid of this as soon as the Language Manager has been refactored into a list table.
	 *
	 * @return int
	 */
	public function get_total_items_number() {

		return (int) $GLOBALS['wpdb']->get_var( "SELECT COUNT(*) as amount FROM `{$this->table_name}`" );
	}
}
