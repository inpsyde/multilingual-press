<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module;

/**
 * Interface for all module manager implementations.
 *
 * @package Inpsyde\MultilingualPress\Module
 * @since   3.0.0
 */
interface ModuleManager {

	/**
	 * Module state.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const MODULE_STATE_ACTIVE = 1;

	/**
	 * Module state.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const MODULE_STATE_ALL = 0;

	/**
	 * Module state.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const MODULE_STATE_INACTIVE = 2;

	/**
	 * Activates the module with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module Module object.
	 */
	public function activate_module( $id );

	/**
	 * Deactivates the module with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module Module object.
	 */
	public function deactivate_module( $id );

	/**
	 * Returns the module with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module Module object.
	 */
	public function get_module( $id );

	/**
	 * Returns all modules with the given state.
	 *
	 * @since 3.0.0
	 *
	 * @param int $state Optional. State of the modules. Defaults to all modules.
	 *
	 * @return Module[] Array of module objects.
	 */
	public function get_modules( $state = self::MODULE_STATE_ALL );

	/**
	 * Checks if the module with the given ID has been registered.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return bool Whether or not the module with the given ID has been registered.
	 */
	public function has_module( $id );

	/**
	 * Checks if any modules have been registered.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not any modules have been registered.
	 */
	public function has_modules();

	/**
	 * Checks if the module with the given ID is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return bool Whether or not the module with the given ID is active.
	 */
	public function is_module_active( $id );

	/**
	 * Registers the given module.
	 *
	 * @since 3.0.0
	 *
	 * @param Module $module Module object.
	 *
	 * @return bool Whether or not the module is active.
	 */
	public function register_module( Module $module );

	/**
	 * Saves the modules persistently.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the modules were saved successfully.
	 */
	public function save_modules();

	/**
	 * Unregisters the module with the given.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module[] Array of all registered module objects.
	 */
	public function unregister_module( $id );
}
