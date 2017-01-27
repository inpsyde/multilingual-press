<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Network_Site_Settings_Properties
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Properties {

	/**
	 * @return string
	 */
	public function get_param_name() {
		return 'extra';
	}

	/**
	 * @return string
	 */
	public function get_param_value() {
		return 'mlp-site-settings';
	}

	/**
	 * @return string
	 */
	public function get_tab_title() {
		return __( 'MultilingualPress', 'multilingual-press' );
	}

	/**
	 * @return string
	 */
	public function get_tab_id() {
		return 'mlp_settings_tab';
	}
}
