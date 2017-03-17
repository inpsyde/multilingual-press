<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting\User;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

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

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta_key User meta key.
	 * @param Nonce  $nonce    Optional. Nonce object. Defaults to null.
	 */
	public function __construct( string $meta_key, Nonce $nonce = null ) {

		$this->meta_key = (string) $meta_key;

		$this->nonce = $nonce;
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
	public function update( $user_id ): bool {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( $this->nonce && ! $this->nonce->is_valid() ) {
			return false;
		}

		$value = $this->get_value();

		return $value
			? (bool) update_user_meta( $user_id, $this->meta_key, $value )
			: (bool) delete_user_meta( $user_id, $this->meta_key );
	}

	/**
	 * Returns the value included in the request.
	 *
	 * @return string The value included in the request.
	 */
	private function get_value(): string {

		$value = is_string( $_GET[ $this->meta_key ] ?? null ) ? $_GET[ $this->meta_key ] : '';

		$request_method = $_SERVER['REQUEST_METHOD'] ?? '';
		if ( ! $request_method || 'POST' !== strtoupper( $request_method ) ) {
			return $value;
		}

		return is_string( $_POST[ $this->meta_key ] ?? null ) ? $_POST[ $this->meta_key ] : '';
	}
}
