<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Network_Site_Settings_Tab_Data
 *
 * @version 2014.07.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Tab_Data implements Mlp_Options_Page_Data {

	/**
	 * Not needed here
	 * @return void
	 */
	public function get_title() {}

	/**
	 * @return string
	 */
	public function get_form_action() {
		return esc_url( admin_url( 'admin-post.php' ) );
	}

	/**
	 * @return string
	 */
	public function get_nonce_action() {
		return 'mlp_network_site_settings';
	}

	/**
	 * @return string
	 */
	public function get_nonce_name() {
		return 'mlp_network_site_settings_nonce';
	}

	/**
	 * @return string
	 */
	public function get_action_name() {
		return 'mlp_network_site_settings';
	}
}