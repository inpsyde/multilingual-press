<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Network_Site_Settings_Properties_Interface
 *
 * @version 2014.07.16
 * @author  toscho
 * @license GPL
 */
interface Mlp_Network_Site_Settings_Properties_Interface {

	/**
	 * @return string
	 */
	public function get_param_name();

	/**
	 * @return mixed
	 */
	public function get_param_value();

	/**
	 * @return string
	 */
	public function get_tab_title();

	/**
	 * @return string
	 */
	public function get_tab_id();
}
