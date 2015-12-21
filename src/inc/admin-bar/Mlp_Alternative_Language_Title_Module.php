<?php # -*- coding: utf-8 -*-

/**
 * Setting model for the Alternative Language Title module.
 */
class Mlp_Alternative_Language_Title_Module {

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $module_manager;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param Mlp_Module_Manager_Interface $module_manager Module manager object.
	 */
	public function __construct( Mlp_Module_Manager_Interface $module_manager ) {

		$this->module_manager = $module_manager;
	}

	/**
	 * Registers the module.
	 *
	 * @return bool
	 */
	public function setup() {

		$module = array(
			'description'  => __(
				'Show sites with their alternative language title in the admin bar.',
				'multilingual-press'
			),
			'display_name' => __( 'Alternative Language Title', 'multilingual-press' ),
			'slug'         => 'class-' . __CLASS__,
			'state'        => 'off',
		);

		return $this->module_manager->register( $module );
	}
}
