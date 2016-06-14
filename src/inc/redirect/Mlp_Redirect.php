<?php # -*- coding: utf-8 -*-

/**
 * Main controller for the Redirect feature.
 */
class Mlp_Redirect {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $modules;

	/**
	 * @var string
	 */
	private $option = 'inpsyde_multilingual_redirect';

	/**
	 * Constructor.
	 *
	 * @param Mlp_Module_Manager_Interface $modules
	 * @param Mlp_Language_Api_Interface   $language_api
	 * @param                              $deprecated
	 */
	public function __construct(
		Mlp_Module_Manager_Interface $modules,
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

		$validator   = new Mlp_Language_Header_Validator();
		$parser      = new Mlp_Accept_Header_Parser( $validator );
		$negotiation = new Mlp_Language_Negotiation( $this->language_api, $parser );
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

		$controller = new Mlp_Redirect_Column( null, null );
		$controller->setup();
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
