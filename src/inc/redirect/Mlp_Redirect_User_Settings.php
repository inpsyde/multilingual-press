<?php # -*- coding: utf-8 -*-

/**
 * Allows users to disable automatic redirects in their profile.
 */
class Mlp_Redirect_User_Settings {

	/**
	 * @var string
	 */
	private $meta_key = 'mlp_redirect';

	/**
	 * Initializes the objects.
	 *
	 * @return void
	 */
	public function setup() {

		$nonce = new Inpsyde_Nonce_Validator( $this->meta_key );

		$user_settings_controller = new Mlp_User_Settings_Controller(
			new Mlp_Redirect_User_Settings_Html( $this->meta_key, $nonce ),
			new Mlp_User_Settings_Updater( $this->meta_key, $nonce )
		);
		$user_settings_controller->setup();

		$redirect_filter = new Mlp_Redirect_Filter( $this->meta_key );
		add_filter( 'mlp_do_redirect', array( $redirect_filter, 'filter_redirect' ) );
	}
}
