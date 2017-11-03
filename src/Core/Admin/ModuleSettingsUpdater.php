<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Module settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class ModuleSettingsUpdater {

	/**
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVE_MODULES = 'multilingualpress.save_modules';

	/**
	 * Input name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const NAME_MODULE_SETTINGS = 'multilingualpress_modules';

	/**
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * @var array
	 */
	private $modules = [];

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 * @param Nonce         $nonce          Nonce object.
	 */
	public function __construct( ModuleManager $module_manager, Nonce $nonce ) {

		$this->module_manager = $module_manager;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the plugin settings according to the data in the request.
	 *
	 * @since 3.0.0
	 *
	 * @param Request $request HTTP request object.
	 *
	 * @return bool Whether or not the settings were updated successfully.
	 */
	public function update_settings( Request $request ): bool {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		$this->modules = $request->body_value(
				self::NAME_MODULE_SETTINGS,
				INPUT_POST,
				FILTER_UNSAFE_RAW,
				FILTER_REQUIRE_ARRAY
			) ?? [];
		if ( ! $this->modules ) {
			return false;
		}

		array_walk( array_keys( $this->module_manager->get_modules() ), [ $this, 'update_module' ] );

		$this->module_manager->save_modules();

		/**
		 * Fires right after the module settings have been updated, and right before the redirect.
		 *
		 * @since 3.0.0
		 *
		 * @param Request $request HTTP request object.
		 */
		do_action( self::ACTION_SAVE_MODULES, $request );

		return true;
	}

	/**
	 * Updates a single module according to the data in the request.
	 *
	 * @param string $id Module ID.
	 *
	 * @return void
	 */
	private function update_module( string $id ) {

		if ( empty( $this->modules[ $id ] ) ) {
			$this->module_manager->deactivate_module( $id );
		} else {
			$this->module_manager->activate_module( $id );
		}
	}
}
