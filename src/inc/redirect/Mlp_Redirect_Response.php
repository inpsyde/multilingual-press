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

		if ( ! empty( $_GET['noredirect'] ) ) {
			$this->save_session( $_GET['noredirect'] );

			return false;
		}

		$redirect_match = $this->negotiation->get_redirect_match();

		$current_site_id = get_current_blog_id();

		if ( $redirect_match['site_id'] === $current_site_id ) {
			return false;
		}

		/**
		 * Filters the redirect URL.
		 *
		 * @param string $url             Redirect URL.
		 * @param array  $redirect_match  Redirect match. {
		 *                                    'priority' => int
		 *                                    'url'      => string
		 *                                    'language' => string
		 *                                    'site_id'  => int
		 *                                }
		 * @param int    $current_site_id Current site ID.
		 */
		$url = (string) apply_filters( 'mlp_redirect_url', $redirect_match['url'], $redirect_match, $current_site_id );
		if ( ! $url ) {
			return false;
		}

		$this->save_session( $redirect_match['language'] );

		wp_redirect( $url );
		mlp_exit();

		return true;
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
