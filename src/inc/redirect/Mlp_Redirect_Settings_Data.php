<?php
/**
 * Save site settings for redirect feature.
 *
 * @version 2014.04.26
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Settings_Data implements Mlp_Redirect_Settings_Data_Interface {

	/**
	 * @var string
	 */
	private $option_name;

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * Constructor.
	 *
	 * @param Inpsyde_Nonce_Validator_Interface $nonce
	 * @param string                            $option_name
	 */
	public function __construct(
		Inpsyde_Nonce_Validator_Interface $nonce,
		$option_name = 'inpsyde_multilingual_redirect'
	) {

		$this->nonce       = $nonce;
		$this->option_name = $option_name;
	}

	/**
	 * Validate and save user input
	 *
	 * @param  array $data User input
	 * @return bool
	 */
	public function save( Array $data ) {

		if ( ! $this->nonce->is_valid() )
			return FALSE;

		$id    = $this->get_current_blog_id( $data, get_current_blog_id() );
		$value = $this->get_sent_value( $data );

		return update_blog_option( $id, $this->option_name, $value );
	}

	/**
	 * Name attribute for the view's checkbox.
	 *
	 * @return string
	 */
	public function get_checkbox_name() {

		return $this->option_name;
	}

	/**
	 * @return int
	 */
	public function get_current_option_value() {

		$id = $this->get_current_blog_id( $_REQUEST );

		return (int) get_blog_option( $id, $this->option_name );
	}

	/**
	 * @param  array $data
	 * @param  int   $default
	 * @return int
	 */
	private function get_current_blog_id( Array $data, $default = 0 ) {

		if ( isset ( $data[ 'id' ] ) )
			return (int) $data[ 'id' ];

		return $default;
	}

	/**
	 * @param  array $data
	 * @return int
	 */
	private function get_sent_value( Array $data ) {

		if ( ! isset ( $data[ $this->option_name ] ) )
			return 0;

		$value = (int) $data[ $this->option_name ];

		return ( 1 < $value ) ? 1 : $value;
	}
}