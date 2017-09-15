<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Module_Manager_Interface
 *
 * @version 2014.07.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Module_Manager_Interface {

	/**
	 * Constructor.
	 *
	 * @param string $option_name
	 */
	public function __construct( $option_name );

	/**
	 * Save option to database.
	 *
	 * @return bool true if saving was successful, false if not.
	 */
	public function save();

	/**
	 * Register a module.
	 *
	 * @param  array $module Required: slug, description and display_name
	 * @return bool true if the module is active, false if it isn't.
	 */
	public function register( array $module );

	/**
	 * Remove a module from list. Does not remove the entry in the option.
	 *
	 * @param string $slug
	 */
	public function unregister( $slug );

	/**
	 * Check whether a module is active.
	 *
	 * @param  string $slug
	 * @return bool
	 */
	public function is_active( $slug );

	/**
	 * Update activation status of a module.
	 *
	 * @uses   Mlp_Module_Manager::update_module()
	 * @param  string $slug
	 * @return array
	 */
	public function activate( $slug );

	/**
	 * Deactivate a module
	 *
	 * @param string $slug
	 */
	public function deactivate( $slug );

	/**
	 * Update one single module.
	 *
	 * @param  string $slug
	 * @param  string $key
	 * @param  string $value
	 * @return array Complete module data.
	 */
	public function update_module( $slug, $key, $value );

	/**
	 * Get all modules or specific states.
	 *
	 * @param  string $status 'all', 'active' or 'inactive'.
	 * @return array
	 */
	public function get_modules( $status = 'all' );

	/**
	 * Get all data of one module.
	 *
	 * @param  string $slug
	 * @return array
	 */
	public function get_module( $slug );

	/**
	 * Check if we have any registered modules.
	 *
	 * @return bool
	 */
	public function has_modules();
}
