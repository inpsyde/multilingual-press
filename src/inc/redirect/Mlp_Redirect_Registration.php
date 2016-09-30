<?php

use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Handles the registration of the redirect feature.
 *
 * @version 2014.04.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Registration {

	/**
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * Constructor
	 *
	 * @param ModuleManager $module_manager
	 */
	public function __construct( ModuleManager $module_manager ) {
		$this->module_manager   = $module_manager;
	}

	/**
	 * Register the feature.
	 *
	 * @return bool Feature is active or not.
	 */
	public function setup() {

		return $this->module_manager->register_module( new Module( 'redirect', [
			'description' => __( 'Redirect visitors according to browser language settings.', 'multilingual-press' ),
			'name'        => __( 'HTTP Redirect', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
