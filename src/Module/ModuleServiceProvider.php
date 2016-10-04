<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module;

use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;

/**
 * Interface for all module service provider implementations to be used for dependency management.
 *
 * @package Inpsyde\MultilingualPress\Module
 * @since   3.0.0
 */
interface ModuleServiceProvider extends BootstrappableServiceProvider {

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registerd successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager );
}
