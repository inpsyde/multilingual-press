<?php # -*- coding: utf-8 -*-

/**
 * Interface Mlp_Options_Page_Data
 *
 * @version 2015.06.30
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Options_Page_Data {

	/**
	 * @return string
	 */
	public function get_title();

	/**
	 * @return string
	 */
	public function get_form_action();

	/**
	 * @return string
	 */
	public function get_nonce_action();

	/**
	 * @return string
	 */
	public function get_nonce_name();

	/**
	 * @return string
	 */
	public function get_action_name();

}
