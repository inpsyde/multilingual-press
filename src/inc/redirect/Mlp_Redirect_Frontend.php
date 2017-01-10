<?php # -*- coding: utf-8 -*-

/**
 * Redirects visitors to the best matching language alternative.
 */
class Mlp_Redirect_Frontend {

	/**
	 * @var Mlp_Redirect_Response_Interface
	 */
	private $response;

	/**
	 * @var string
	 */
	private $option_name;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Redirect_Response_Interface $response    Redirect response object.
	 * @param string                          $option_name Option name.
	 */
	public function __construct( Mlp_Redirect_Response_Interface $response, $option_name ) {

		$this->response = $response;

		$this->option_name = $option_name;
	}

	/**
	 * Initializes the redirect data and response classes.
	 *
	 * @return void
	 */
	public function setup() {

		if ( $this->is_redirectable() ) {
			add_action( 'template_redirect', [ $this->response, 'redirect' ], 1 );
		}
	}

	/**
	 * Checks if the current request should be redirected.
	 *
	 * Requires an accept header, the redirect feature being active for the current site, and the current language not
	 * being included in the $_SESSION's noredirect element.
	 *
	 * @return bool
	 */
	public function is_redirectable() {

		if ( empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			return false;
		}

		if ( ! get_option( $this->option_name ) ) {
			return false;
		}

		if ( ! isset( $_SESSION ) && ! session_id() ) {
			session_start();
		}

		if ( isset( $_SESSION['noredirect'] ) ) {
			$current_site_language = \Inpsyde\MultilingualPress\get_current_site_language();
			if ( in_array( $current_site_language, (array) $_SESSION['noredirect'], true ) ) {
				return false;
			}
		}

		/**
		 * Filters if the current request should be redirected.
		 *
		 * @param bool $redirect Redirect the current request?
		 */
		return (bool) apply_filters( 'mlp_do_redirect', true );
	}
}
