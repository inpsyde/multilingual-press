<?php # -*- coding: utf-8 -*-

class Mlp_Network_Site_Settings_Tab_Data implements Mlp_Options_Page_Data {

	// not needed here
	public function get_title() {}

	public function get_form_action() {
		return esc_url( admin_url( 'admin-post.php' ) );
	}
	public function get_nonce_action() {
		return 'mlp_network_site_settings';
	}
	public function get_nonce_name() {
		return 'mlp_network_site_settings_nonce';
	}
	public function get_action_name() {
		return 'mlp_network_site_settings';
	}
}