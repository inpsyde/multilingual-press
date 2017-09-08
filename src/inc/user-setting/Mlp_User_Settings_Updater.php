<?php
/**
 * Simple user meta field updater.
 *
 * @version 2014.07.05
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_User_Settings_Updater implements Mlp_User_Settings_Updater_Interface {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * Constructor.
	 *
	 * @param string                            $key
	 * @param Inpsyde_Nonce_Validator_Interface $nonce
	 */
	public function __construct( $key, Inpsyde_Nonce_Validator_Interface $nonce ) {

		$this->key   = $key;
		$this->nonce = $nonce;
	}

	/**
	 * Save user data.
	 *
	 * @param  int $user_id
	 * @return bool
	 */
	public function save( $user_id ) {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( empty( $_POST[ $this->key ] ) || '' === trim( $_POST[ $this->key ] ) ) {
			return delete_user_meta( $user_id, $this->key );
		}

		return update_user_meta( $user_id, $this->key, $_POST[ $this->key ] );
	}
}
