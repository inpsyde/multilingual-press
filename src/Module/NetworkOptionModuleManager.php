<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module;

use Inpsyde\MultilingualPress\Module\Exception\InvalidModule;
use Inpsyde\MultilingualPress\Module\Exception\ModuleAlreadyRegistered;

/**
 * Module manager implementation using a network option for storage.
 *
 * @package Inpsyde\MultilingualPress\Module
 * @since   3.0.0
 */
final class NetworkOptionModuleManager implements ModuleManager {

	/**
	 * @var Module[]
	 */
	private $modules = [];

	/**
	 * @var string
	 */
	private $option;

	/**
	 * @var bool[]
	 */
	private $states;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option The name of the network option used for storage.
	 */
	public function __construct( string $option ) {

		$this->option = $option;

		$this->states = (array) get_network_option( null, $this->option, [] );
	}

	/**
	 * Activates the module with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module Module object.
	 *
	 * @throws InvalidModule if there is no module with the given ID.
	 */
	public function activate_module( string $id ): Module {

		if ( ! $this->has_module( $id ) ) {
			throw InvalidModule::for_id( $id, 'activate' );
		}

		$this->states[ $id ] = true;

		return $this->get_module( $id )->activate();
	}

	/**
	 * Deactivates the module with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module Module object.
	 *
	 * @throws InvalidModule if there is no module with the given ID.
	 */
	public function deactivate_module( string $id ): Module {

		if ( ! $this->has_module( $id ) ) {
			throw InvalidModule::for_id( $id, 'deactivate' );
		}

		$this->states[ $id ] = false;

		return $this->get_module( $id )->deactivate();
	}

	/**
	 * Returns the module with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module Module object.
	 *
	 * @throws InvalidModule if there is no module with the given ID.
	 */
	public function get_module( string $id ): Module {

		if ( ! $this->has_module( $id ) ) {
			throw InvalidModule::for_id( $id, 'read' );
		}

		return $this->modules[ $id ];
	}

	/**
	 * Returns all modules with the given state.
	 *
	 * @since 3.0.0
	 *
	 * @param int $state Optional. State of the modules. Defaults to all modules.
	 *
	 * @return Module[] Array of module objects.
	 */
	public function get_modules( int $state = ModuleManager::MODULE_STATE_ALL ): array {

		if ( ! $this->modules ) {
			return [];
		}

		if ( ModuleManager::MODULE_STATE_ACTIVE === $state ) {
			return array_intersect_key( $this->modules, array_filter( $this->states ) );
		}

		if ( ModuleManager::MODULE_STATE_INACTIVE === $state ) {
			return array_diff_key( $this->modules, array_filter( $this->states ) );
		}

		return $this->modules;
	}

	/**
	 * Checks if the module with the given ID has been registered.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return bool Whether or not the module with the given ID has been registered.
	 */
	public function has_module( string $id ): bool {

		return isset( $this->modules[ $id ] );
	}

	/**
	 * Checks if any modules have been registered.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not any modules have been registered.
	 */
	public function has_modules(): bool {

		return ! empty( $this->modules );
	}

	/**
	 * Checks if the module with the given ID is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return bool Whether or not the module with the given ID is active.
	 */
	public function is_module_active( string $id ): bool {

		return (bool) ( $this->states[ $id ] ?? false );
	}

	/**
	 * Registers the given module.
	 *
	 * @since 3.0.0
	 *
	 * @param Module $module Module object.
	 *
	 * @return bool Whether or not the module is active.
	 *
	 * @throws ModuleAlreadyRegistered if a module with the ID of the given module already has been registered.
	 */
	public function register_module( Module $module ): bool {

		$id = $module->id();

		if ( $this->has_module( $id ) ) {
			throw ModuleAlreadyRegistered::for_id( $id, 'register' );
		}

		if ( isset( $this->states[ $id ] ) ) {
			if ( $this->states[ $id ] ) {
				$module->activate();
			} else {
				$module->deactivate();
			}
		} else {
			$this->states[ $id ] = $module->is_active();

			$this->save_modules();
		}

		$this->modules[ $id ] = $module;

		return $this->states[ $id ];
	}

	/**
	 * Saves the modules persistently.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the modules were saved successfully.
	 */
	public function save_modules(): bool {

		return update_network_option( null, $this->option, $this->states );
	}

	/**
	 * Unregisters the module with the given.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Module ID.
	 *
	 * @return Module[] Array of all registered module objects.
	 */
	public function unregister_module( string $id ): array {

		unset( $this->modules[ $id ], $this->states[ $id ] );

		return $this->modules;
	}
}
