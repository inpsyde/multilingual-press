<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Admin\SitesListTableColumn;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Main controller for the Redirect feature.
 */
class Mlp_Redirect {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @var ModuleManager
	 */
	private $modules;

	/**
	 * @var string
	 */
	private $option = 'inpsyde_multilingual_redirect';

	/**
	 * Constructor.
	 *
	 * @param ModuleManager $modules
	 * @param Mlp_Language_Api_Interface   $language_api
	 * @param                              $deprecated
	 */
	public function __construct(
		ModuleManager $modules,
		Mlp_Language_Api_Interface $language_api,
		$deprecated
	) {

		$this->modules = $modules;

		$this->language_api = $language_api;
	}

	/**
	 * Determines the current state and actions, and calls subsequent methods.
	 *
	 * @return bool
	 */
	public function setup() {

		if ( ! $this->register_setting() ) {
			return false;
		}

		$this->user_settings();

		if ( ! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			$this->frontend_redirect();

			return true;
		}

		$this->site_settings();

		if ( is_network_admin() ) {
			$this->activation_column();
		}

		return true;
	}

	/**
	 * Redirects visitors to the best matching language alternative.
	 *
	 * @return void
	 */
	private function frontend_redirect() {

		$negotiation = new Mlp_Language_Negotiation( $this->language_api );
		$response    = new Mlp_Redirect_Response( $negotiation );
		$controller  = new Mlp_Redirect_Frontend( $response, $this->option );
		$controller->setup();
	}

	/**
	 * Shows the redirect status in the sites list.
	 *
	 * @return void
	 */
	private function activation_column() {

		( new SitesListTableColumn(
			'multilingualpress.redirect',
			__( 'Redirect', 'multilingual-press' ),
			function ( $id, $site_id ) {

				// TODO: Don't hard-code option name.
				return get_blog_option( $site_id, 'inpsyde_multilingual_redirect' )
					? '<span class="dashicons dashicons-yes"></span>'
					: '';
			}
		) )->register();
	}

	/**
	 * Sets up user-specific settings.
	 *
	 * @return void
	 */
	private function user_settings() {

		$controller = new Mlp_Redirect_User_Settings();
		$controller->setup();
	}

	/**
	 * Sets up site-specific settings.
	 *
	 * @return void
	 */
	private function site_settings() {

		$controller = new Mlp_Redirect_Site_Settings( $this->option );
		$controller->setup();
	}

	/**
	 * Registers the settings.
	 *
	 * @return bool
	 */
	private function register_setting() {

		$controller = new Mlp_Redirect_Registration( $this->modules );

		return $controller->setup();
	}
}
