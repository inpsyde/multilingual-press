<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Db_Table_List_Interface
 *
 * @version 2014.08.19
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Db_Table_List_Interface {

	/**
	 * Tables for the network only, not site specific, not custom.
	 *
	 * @return array
	 */
	public function get_all_table_names();

	/**
	 * Get standard network tables
	 *
	 * @api
	 * @return array
	 */
	public function get_network_core_tables();

	/**
	 * Get standard site tables
	 *
	 * @api
	 * @param  int   $site_id
	 * @return array
	 */
	public function get_site_core_tables( $site_id );

	/**
	 * Get custom site tables
	 *
	 * Might return custom network tables from other plugins if the site id is
	 * the network id.
	 *
	 * @api
	 * @param  int   $site_id
	 * @return array
	 */
	public function get_site_custom_tables( $site_id );

	/**
	 * Get this plugin's table names
	 *
	 * @api
	 * @return array
	 */
	public function get_mlp_tables();
}