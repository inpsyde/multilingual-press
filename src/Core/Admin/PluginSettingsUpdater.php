<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

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
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_UPDATE_PLUGIN_SETTINGS = 'multilingualpress.update_plugin_settings';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Nonce   $nonce   Nonce object.
	 * @param Request $request HTTP request object.
	 */
	public function __construct( Nonce $nonce, Request $request ) {

		$this->nonce = $nonce;

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

		/**
		 * Fires when the plugin settings are about to get updated.
		 *
		 * @since 3.0.0
		 *
		 * @param Request $request HTTP request object.
		 */
		do_action( self::ACTION_UPDATE_PLUGIN_SETTINGS, $this->request );

		redirect_after_settings_update();
	}
}
