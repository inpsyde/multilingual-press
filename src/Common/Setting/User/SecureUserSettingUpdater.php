<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Setting\User;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
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
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $meta_key User meta key.
	 * @param Request $request  HTTP request object.
	 * @param Nonce   $nonce    Optional. Nonce object. Defaults to null.
	 */
	public function __construct( string $meta_key, Request $request, Nonce $nonce = null ) {

		$this->meta_key = (string) $meta_key;

		$this->nonce = $nonce;

		$this->request = $request;
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

		$value = $this->request->body_value( $this->meta_key, INPUT_REQUEST, FILTER_SANITIZE_STRING );
		if ( ! is_string( $value ) ) {
			$value = '';
		}

		return $value
			? (bool) update_user_meta( $user_id, $this->meta_key, $value )
			: (bool) delete_user_meta( $user_id, $this->meta_key );
	}
}
