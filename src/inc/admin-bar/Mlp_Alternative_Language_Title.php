<?php # -*- coding: utf-8 -*-

/**
 * Main controller for the Alternative Language Title feature.
 */
class Mlp_Alternative_Language_Title {

	/**
	 * @var Mlp_Admin_Bar_Customizer
	 */
	private $customizer;

	/**
	 * @var Mlp_Alternative_Language_Title_Module
	 */
	private $module;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param Mlp_Alternative_Language_Title_Module $module     Module object.
	 * @param Mlp_Admin_Bar_Customizer              $customizer Admin bar customizer.
	 */
	public function __construct( Mlp_Alternative_Language_Title_Module $module, Mlp_Admin_Bar_Customizer $customizer ) {

		$this->module = $module;

		$this->customizer = $customizer;
	}

	/**
	 * Sets up the module, and wires up all functions.
	 *
	 * @return bool
	 */
	public function setup() {

		add_action( 'mlp_blogs_save_fields', array( $this->customizer, 'update_cache' ) );

		if ( ! $this->module->setup() ) {
			return false;
		}

		add_filter( 'admin_bar_menu', array( $this->customizer, 'replace_site_nodes' ), 11 );

		if ( ! is_network_admin() ) {
			add_filter( 'admin_bar_menu', array( $this->customizer, 'replace_site_name' ), 31 );
		}

		return true;
	}
}
