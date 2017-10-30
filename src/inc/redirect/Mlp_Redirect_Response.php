<?php # -*- coding: utf-8 -*-

/**
 * Sends redirect headers.
 */
class Mlp_Redirect_Response implements Mlp_Redirect_Response_Interface {

	/**
	 * @var Mlp_Language_Negotiation_Interface
	 */
	private $negotiation;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Language_Negotiation_Interface $negotiation Language negotiation object.
	 */
	public function __construct( Mlp_Language_Negotiation_Interface $negotiation ) {

		$this->negotiation = $negotiation;
	}

	/**
	 * Redirects if needed.
	 *
	 * @return bool
	 */
	public function redirect() {

		$language = (string) filter_input( INPUT_GET, 'noredirect' );
		if ( '' !== $language ) {
			$this->save_session( $language );

			return false;
		}

		$redirect_match = $this->negotiation->get_redirect_match();

		$current_site_id = get_current_blog_id();

		if ( $redirect_match['site_id'] === $current_site_id ) {
			return false;
		}

		if ( empty( $redirect_match['url'] ) ) {
			return false;
		}

		if ( ! isset( $redirect_match['language'] ) ) {
			return false;
		}

		$this->save_session( $redirect_match['language'] );

		wp_redirect( $redirect_match['url'] );
		mlp_exit();

		return true;
	}

	/**
	 * Registers the redirection using the appropriate hook.
	 *
	 * @return void
	 */
	public function register() {

		add_action( 'template_redirect', array( $this, 'redirect' ), 1 );
	}

	/**
	 * Saves the given language to the noredirect data in the $_SESSION superglobal.
	 *
	 * @param string $language Language code.
	 *
	 * @return void
	 */
	private function save_session( $language ) {

		if ( ! isset( $_SESSION ) && ! session_id() ) {
			session_start();
		}

		if ( ! isset( $_SESSION['noredirect'] ) ) {
			$_SESSION['noredirect'] = array();
		} else {
			$_SESSION['noredirect'] = (array) $_SESSION['noredirect'];

			if ( in_array( $language, $_SESSION['noredirect'], true ) ) {
				return;
			}
		}

		$_SESSION['noredirect'][] = $language;
	}
}
