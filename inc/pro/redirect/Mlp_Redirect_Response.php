<?php
/**
 * Send redirect headers.
 *
 * @version    2014.09.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Response implements Mlp_Redirect_Response_Interface {

	/**
	 * @type Mlp_Language_Negotiation_Interface
	 */
	private $negotiation;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Language_Negotiation_Interface $negotiation
	 */
	public function __construct( Mlp_Language_Negotiation_Interface $negotiation ) {

		$this->negotiation = $negotiation;
	}
	/**
	 * Redirect if needed.
	 *
	 * @return void
	 */
	public function redirect() {

		if ( ! empty ( $_GET[ 'noredirect' ] ) )
			return;

		$match = $this->negotiation->get_redirect_match();

		if ( $match[ 'site_id' ] === get_current_blog_id() )
			return;

		$url             = $match[ 'url' ];
		$current_site_id = get_current_blog_id();

		/**
		 * Change the URL for redirects. You might add support for other types
		 * than singular posts and home here.
		 * If you return an empty value, the redirect will not happen.
		 * The result will be validated with esc_url().
		 *
		 * @param string $url   might be empty
		 * @param array  $match
		 *                      - priority
		 *                      - url
		 *                      - language
		 *                      - site_id
		 * @param int    $current_site_id Current blog id
		 */
		$url = apply_filters( 'mlp_redirect_url', $url, $match, $current_site_id );

		if ( empty ( $url ) )
			return; // no URL found

		// check to only session_start if no session is active
		if ( ! isset ( $_SESSION ) && ! session_id() )
			session_start();

		if ( isset ( $_SESSION[ 'noredirect' ] ) ) {
			$existing = (array) $_SESSION[ 'noredirect' ];

			if ( in_array( $match[ 'language' ], $existing ) )
				return;
		}

		$_SESSION[ 'noredirect' ][] = $match[ 'language' ];

		wp_redirect( $url ); // finally!
		exit;
	}
}