<?php
/**
 * Redirect visitors to the best matching language alternative.
 *
 * @version 2014.09.26
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Frontend {

	/**
	 * @type Mlp_Redirect_Response_Interface
	 */
	private $response;

	/**
	 * @type string
	 */
	private $option_name;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Redirect_Response_Interface $response
	 * @param string                          $option_name
	 */
	public function __construct(
		Mlp_Redirect_Response_Interface $response,
		                                $option_name
	) {

		$this->response    = $response;
		$this->option_name = $option_name;
	}

	/**
	 * Initialize redirect data and response classes.
	 *
	 * @return void
	 */
	public function setup() {

		if ( ! $this->is_redirectable() )
			return;

		add_action( 'template_redirect', array ( $this->response, 'redirect' ), 1 );

		add_filter(
			'mlp_linked_element_link',
			array ( $this, 'add_noredirect_parameter' ), 10, 2
		);
	}

	/**
	 * add noredirect query var to the links
	 *
	 * @wp-hook mlp_linked_element_link
	 * @param   string $link
	 * @param   int    $site_id
	 * @return  string
	 */
	public function add_noredirect_parameter( $link, $site_id ) {

		$link = (string) $link;

		if ( empty ( $link ) )
			return $link;

		$languages = mlp_get_available_languages();

		if ( empty ( $languages[ $site_id ] ) )
			return $link;

		return add_query_arg( 'noredirect', $languages[ $site_id ], $link );
	}

	/**
	 * Is there an accept header and the feature is active for the site?
	 *
	 * @return bool
	 */
	public function is_redirectable() {

		if ( empty ( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
			return FALSE;

		/**
		 * Use this hook to intercept the redirection early.
		 *
		 * @param bool TRUE Should the redirect happen or not.
		 */
		if ( ! apply_filters( 'mlp_do_redirect', TRUE ) )
			return FALSE;

		return (bool) get_option( $this->option_name );
	}
}