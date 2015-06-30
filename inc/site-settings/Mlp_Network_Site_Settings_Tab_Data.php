<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_Network_Site_Settings_Tab_Data
 *
 * @version 2015.06.30
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Tab_Data implements Mlp_Options_Page_Data {

	/**
	 * @return string
	 */
	public function get_title() {

		return '';
	}

	/**
	 * @return string
	 */
	public function get_form_action() {

		$admin_url = admin_url( 'admin-post.php' );

		return esc_url( $admin_url );
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
