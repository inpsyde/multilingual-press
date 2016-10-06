<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\User;

/**
 * User setting updater implementation validating a nonce specific to the update action included in the request data.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\User
 * @since   3.0.0
 */
final class SecureUserSettingUpdater implements UserSettingUpdater {

	/**
	 * @var string
	 */
	private $meta_key;

	/**@todo Adapt class.
	 * @var object
	 */
	private $validator;

	/**@todo Adapt validator class.
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta_key  User meta key.
	 * @param object $validator Optional. Validator object. Defaults to null.
	 */
	public function __construct( $meta_key, $validator = null ) {

		$this->meta_key = (string) $meta_key;

		$this->validator = $validator;
	}

	/**
	 * Updates the setting with the data in the request for the user with the given ID.
	 *
	 * @since   3.0.0
	 * @wp-hook profile_update
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool Whether or not the user setting was updated successfully.
	 */
	public function update( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		// TODO: Well, actually validate as soon as the Nonce namespace has been refactored.
		if ( $this->validator /* && ! $this->validator->validate() */ ) {
			return false;
		}

		$value = $this->get_value();

		return $value
			? update_user_meta( $user_id, $this->meta_key, $value )
			: delete_user_meta( $user_id, $this->meta_key );
	}

	/**
	 * Returns the value included in the request.
	 *
	 * @return string The value included in the request.
	 */
	private function get_value() {

		$value = array_key_exists( $this->meta_key, $_GET )
			? $_GET[ $this->meta_key ]
			: '';

		if ( empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return $value;
		}

		return array_key_exists( $this->meta_key, $_POST )
			? $_POST[ $this->meta_key ]
			: '';
	}
}
