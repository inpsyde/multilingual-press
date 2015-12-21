<?php # -*- coding: utf-8 -*-
/**
 * Manage modules, default and pro features.
 *
 * @author     Inpsyde GmbH, toscho
 * @since      1.2
 * @version    2014.07.17
 * @package    MultilingualPress
 */
class Mlp_Module_Manager implements Mlp_Module_Manager_Interface {

	/**
	 * Name of module option.
	 *
	 * @type string
	 */
	private $option_name = '';

	/**
	 * Module data.
	 *
	 * @type array
	 */
	private $modules = array ();

	/**
	 * Activation status. Saved in option.
	 *
	 * @type array
	 */
	private $states = array ();

	/**
	 * Constructor.
	 *
	 * @param string $option_name
	 */
	public function __construct( $option_name ) {

		$this->option_name = $option_name;
		$this->states      = get_site_option( $option_name, array () );
	}

	/**
	 * Save option to database.
	 *
	 * @return bool TRUE if saving was successful, FALSE if not.
	 */
	public function save() {

		$return       = update_site_option( $this->option_name, $this->states );
		$this->states = get_site_option( $this->option_name, array () );

		return $return;
	}

	/**
	 * Register a module.
	 *
	 * @param  array $module Required: slug, description and display_name
	 * @return bool TRUE if the module is active, FALSE if it isn't.
	 */
	public function register( Array $module ) {

		$slug = $module[ 'slug' ];

		if ( ! isset( $this->states[ $slug ] ) ) {
			$state = 'off';
			if ( ! isset( $module['state'] ) || 'off' !== $module['state'] ) {
				$state = 'on';
			}
			$this->states[ $slug ] = $state;
			$this->save();
		}

		$module[ 'state' ] = $this->states[ $slug ];

		$this->modules[ $slug ] = $module;

		return $this->is_active( $slug );
	}

	/**
	 * Remove a module from list. Does not remove the entry in the option.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function unregister( $slug ) {

		if ( ! isset ( $this->modules[ $slug ] ) )
			return FALSE;

		unset ( $this->modules[ $slug ] );

		return TRUE;
	}

	/**
	 * Check whether a module is active.
	 *
	 * @param  string $slug
	 * @return bool
	 */
	public function is_active( $slug ) {

		if ( ! isset ( $this->states[ $slug ] ) )
			return FALSE;

		return 'on' === $this->states[ $slug ];
	}

	/**
	 * Update activation status of a module.
	 *
	 * @uses   Mlp_Module_Manager::update_module()
	 * @param  string $slug
	 * @return array
	 */
	public function activate( $slug ) {
		return $this->update_module( $slug, 'state', 'on' );
	}

	/**
	 * Update activation status of a module.
	 *
	 * @uses   Mlp_Module_Manager::update_module()
	 * @param  string $slug
	 * @return array
	 */
	public function deactivate( $slug ) {
		return $this->update_module( $slug, 'state', 'off' );
	}

	/**
	 * Update one single module.
	 *
	 * @param  string $slug
	 * @param  string $key
	 * @param  string $value
	 * @return array Complete module data.
	 */
	public function update_module( $slug, $key, $value ) {

		$this->modules[ $slug ][ $key ] = $value;

		if ( 'state' === $key )
			$this->states[ $slug ] = $value;

		return $this->modules[ $slug ];
	}

	/**
	 * Get all modules or specific states.
	 *
	 * @param  string $status 'all', 'active' or 'inactive'.
	 * @return array
	 */
	public function get_modules( $status = 'all' ) {

		if ( 'all' === $status )
			return $this->modules;

		// Filter modules by state
		$find = 'active' === $status ? 'on' : 'off';

		return $this->get_modules_by_status( $find );
	}

	/**
	 * @param $status 'on' or 'off'
	 * @return array
	 */
	private function get_modules_by_status( $status ) {

		$out  = array ();

		foreach ( $this->modules as $slug => $module ) {
			if ( $this->states[ $slug ] === $status )
				$out[ $slug ] = $module;
		}

		return $out;
	}

	/**
	 * Get all data of one module.
	 *
	 * @param  string $slug
	 * @return array
	 */
	public function get_module( $slug ) {

		if ( ! isset ( $this->modules[ $slug ] ) )
			return array ();

		return $this->modules[ $slug ];
	}

	/**
	 * Check if we have any registered modules.
	 *
	 * @return bool
	 */
	public function has_modules() {
		return ! empty( $this->modules );
	}
}
