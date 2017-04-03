<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Admin\SettingsPage;
use Inpsyde\MultilingualPress\Common\Http\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\ModuleManager;

use function Inpsyde\MultilingualPress\check_admin_referer;
use function Inpsyde\MultilingualPress\redirect_after_settings_update;

/**
 * Plugin settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class PluginSettingsUpdater {

	/**
	 * Action used for updating plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION = 'update_multilingualpress_settings';

	/**
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var SettingsPage
	 */
	private $settings_page;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 * @param Nonce         $nonce          Nonce object.
	 * @param SettingsPage  $settings_page  Settings page object.
	 * @param Request       $request        HTTP request abstraction
	 */
	public function __construct(
		ModuleManager $module_manager,
		Nonce $nonce,
		SettingsPage $settings_page,
		Request $request
	) {

		$this->module_manager = $module_manager;

		$this->nonce = $nonce;

		$this->settings_page = $settings_page;

		$this->request = $request;
	}

	/**
	 * Updates the plugin settings according to the data in the request.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function update_settings() {

		check_admin_referer( $this->nonce );

		array_walk( array_keys( $this->module_manager->get_modules() ), [ $this, 'update_module' ] );

		$this->module_manager->save_modules();

		/**
		 * Fires right after the module settings have been updated, and right before the redirect.
		 *
		 * @since 3.0.0
		 *
		 * @param Request Request abstraction.
		 */
		do_action( 'multilingualpress.save_modules', $this->request );

		redirect_after_settings_update( $this->settings_page->url() );
	}

	/**
	 * Updates a single module according to the data in the request.
	 *
	 * @param string $id Module ID.
	 *
	 * @return void
	 */
	private function update_module( string $id ) {

		$modules = $this->request->body_value( 'multilingualpress_modules' ) ?: [];

		if ( empty( $modules[ $id ] ) ) {
			$this->module_manager->deactivate_module( $id );
		} else {
			$this->module_manager->activate_module( $id );
		}
	}
}
