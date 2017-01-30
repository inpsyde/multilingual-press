<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Request handler for site settings update requests.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class SiteSettingsUpdateRequestHandler {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION = 'update_multilingualpress_site_settings';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var SiteSettingsUpdater
	 */
	private $updater;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsUpdater $updater Updater object.
	 * @param Nonce               $nonce   Nonce object.
	 */
	public function __construct( SiteSettingsUpdater $updater, Nonce $nonce ) {

		$this->updater = $updater;

		$this->nonce = $nonce;
	}

	/**
	 * Handles POST requests.
	 *
	 * @since   3.0.0
	 * @wp-hook admin_post_{$action}
	 *
	 * @return void
	 */
	public function handle_post_request() {

		if ( ! \Inpsyde\MultilingualPress\check_admin_referer( $this->nonce ) ) {
			wp_die( 'Invalid', 'Invalid', 403 );
		}

		if ( empty( $_REQUEST['id'] ) || ! is_numeric( $_REQUEST['id'] ) ) {
			wp_die( 'Invalid site', 'Invalid site', 403 );
		}

		$this->updater->update_settings( (int) $_REQUEST['id'] );

		\Inpsyde\MultilingualPress\redirect_after_settings_update();
	}
}
