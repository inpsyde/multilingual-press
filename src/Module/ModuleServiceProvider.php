<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Interface for all module service provider implementations to be used for dependency management.
 *
 * @package Inpsyde\MultilingualPress\Module
 * @since   3.0.0
 */
interface ModuleServiceProvider extends ServiceProvider {

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registered successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ): bool;

	/**
	 * Performs various tasks on module activation.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function activate_module( Container $container );
}
