<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Table_Names_Interface
 *
 * @version 2014.08.19
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Table_Names_Interface {

	/**
	 * Tables for the network only, not site specific, not custom.
	 *
	 * @return array
	 */
	public function get_core_network_tables();

	/**
	 * Get all tables for a site, core and custom.
	 *
	 * @return array
	 */
	public function get_all_site_tables();

	/**
	 * Get core table only.
	 *
	 * @return array
	 */
	public function get_core_site_tables();

	/**
	 * Get custom tables for a site.
	 *
	 * @return array
	 */
	public function get_custom_site_tables();
}
