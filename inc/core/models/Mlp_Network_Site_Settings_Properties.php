<?php # -*- coding: utf-8 -*-

class Mlp_Network_Site_Settings_Properties
	implements Mlp_Network_Site_Settings_Properties_Interface {

	private $plugin_data;

	/**
	 * Constructor.
	 */
	public function __construct( Inpsyde_Property_List_Interface $plugin_data ) {
		$this->plugin_data = $plugin_data;
	}

	public function get_param_name() {
		return 'extra';
	}

	public function get_param_value() {
		return 'mlp-site-settings';
	}

	public function get_tab_title() {
		return __( 'Multilingual Press', 'multilingualpress' );
	}

	public function get_tab_id() {
		return 'mlp_settings_tab';
	}
}