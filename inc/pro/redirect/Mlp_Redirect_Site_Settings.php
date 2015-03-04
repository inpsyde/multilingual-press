<?php
/**
 * Site specific settings.
 *
 * @version 2014.04.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Site_Settings {

	/**
	 * @var string
	 */
	private $option_name;

	/**
	 * Constructor.
	 *
	 * @param string $option_name
	 */
	public function __construct( $option_name ) {
		$this->option_name = $option_name;
	}

	/**
	 * Create instances, and register callbacks.
	 *
	 * @return void
	 */
	public function setup() {

		$nonce = new Inpsyde_Nonce_Validator( 'mlp_redirect' );
		$data  = new Mlp_Redirect_Settings_Data( $nonce, $this->option_name );
		$view  = new Mlp_Redirect_Site_Settings_Form( $nonce, $data );

		add_filter( 'mlp_blogs_add_fields',  array ( $view, 'render' ) );
		add_filter( 'mlp_blogs_save_fields', array ( $data, 'save' ) );
	}
}