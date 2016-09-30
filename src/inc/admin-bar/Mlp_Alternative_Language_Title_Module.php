<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Setting model for the Alternative Language Title module.
 */
class Mlp_Alternative_Language_Title_Module {

	/**
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 */
	public function __construct( ModuleManager $module_manager ) {

		$this->module_manager = $module_manager;
	}

	/**
	 * Registers the module.
	 *
	 * @return bool
	 */
	public function setup() {

		return $this->module_manager->register_module( new Module( 'alternative_language_title', [
			'description' => __(
				'Show sites with their alternative language title in the admin bar.',
				'multilingual-press'
			),
			'name'        => __( 'Alternative Language Title', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
