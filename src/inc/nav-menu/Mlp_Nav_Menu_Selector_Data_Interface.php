<?php
/**
 * Interface for Mlp_Language_Nav_Menu_Data
 *
 * @version 2014.07.15
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Nav_Menu_Selector_Data_Interface {

	/**
	 * @return array
	 */
	public function get_list();

	/**
	 * @return string
	 */
	public function get_list_id();

	/**
	 * @return string
	 */
	public function get_button_id();

	/**
	 * @return bool
	 */
	public function has_menu();

	/**
	 * @return array
	 */
	public function get_ajax_menu_items();
}
