<?php
/**
 * Main controller for the redirect feature
 *
 * @version    2014.09.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect {

	/**
	 * @type Mlp_Module_Manager_Interface
	 */
	private $modules;

	/**
	 * @type string
	 */
	private $image_url;

	/**
	 * @type string
	 */
	private $option = 'inpsyde_multilingual_redirect';

	/**
	 * @type Mlp_Language_Api_Interface
	 */
	private $language_api;

	/**
	 * Constructor
	 *
	 * @param Mlp_Module_Manager_Interface $modules
	 * @param Mlp_Language_Api_Interface   $language_api
	 * @param string                       $image_url
	 */
	public function __construct(
		Mlp_Module_Manager_Interface $modules,
		Mlp_Language_Api_Interface   $language_api,
		                             $image_url
	) {
		$this->modules      = $modules;
		$this->image_url    = $image_url;
		$this->language_api = $language_api;
	}

	/**
	 * Determine current state and actions, call subsequent methods.
	 *
	 * @return void
	 */
	public function setup() {

		if ( ! $this->register_setting() ) {
			return;
		}

		if ( ! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			$this->frontend_redirect();

			return;
		}

		$this->site_settings();

		if ( is_network_admin() ) {
			$this->activation_column();
		}
	}

	/**
	 * Redirect visitors to the best matching language alternative
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
	 * Show the redirect status in the sites list
	 *
	 * @return void
	 */
	private function activation_column() {

		$controller = new Mlp_Redirect_Column( $this->option, $this->image_url );
		$controller->setup();
	}

	/**
	 * Site specific settings
	 *
	 * @return void
	 */
	private function site_settings() {

		$controller = new Mlp_Redirect_Site_Settings( $this->option );
		$controller->setup();
	}

	/**
	 * Register the settings.
	 *
	 * @return bool
	 */
	private function register_setting() {

		$controller = new Mlp_Redirect_Registration( $this->modules );
		return $controller->setup();
	}
}
