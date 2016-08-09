<?php # -*- coding: utf-8 -*-

/**
 * Stops the redirect if the user has turned it off.
 */
class Mlp_Redirect_Filter {

	/**
	 * @var int
	 */
	private $current_user_id;

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * Constructor.
	 *
	 * @param string $meta_key Meta key of the redirect user setting.
	 */
	public function __construct( $meta_key ) {

		$this->meta_key = (string) $meta_key;

		$this->current_user_id = get_current_user_id();
	}

	/**
	 * Stops the redirect if the user has turned it off.
	 *
	 * @wp-hook mlp_do_redirect
	 *
	 * @param bool $redirect Redirect the current request?
	 *
	 * @return bool
	 */
	public function filter_redirect( $redirect ) {

		if ( ! $this->current_user_id ) {
			return $redirect;
		}

		$user_setting = (bool) get_user_meta( $this->current_user_id, $this->meta_key );
		if ( $user_setting ) {
			return false;
		}

		return $redirect;
	}
}
