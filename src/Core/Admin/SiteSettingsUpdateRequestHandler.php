<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

use function Inpsyde\MultilingualPress\check_admin_referer;
use function Inpsyde\MultilingualPress\redirect_after_settings_update;

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
	 * @var Request
	 */
	private $request;

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
	 * @param Request             $request HTTP request object.
	 * @param Nonce               $nonce   Nonce object.
	 */
	public function __construct( SiteSettingsUpdater $updater, Request $request, Nonce $nonce ) {

		$this->updater = $updater;

		$this->request = $request;

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

		if ( ! check_admin_referer( $this->nonce ) ) {
			wp_die( 'Invalid', 'Invalid', 403 );
		}

		$site_id = (int) $this->request->body_value( 'id', INPUT_REQUEST, FILTER_SANITIZE_NUMBER_INT );
		if ( ! $site_id ) {
			wp_die( 'Invalid site', 'Invalid site', 403 );
		}

		$this->updater->update_settings( $site_id );

		redirect_after_settings_update();
	}
}
