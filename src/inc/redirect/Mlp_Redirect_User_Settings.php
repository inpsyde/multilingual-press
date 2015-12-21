<?php # -*- coding: utf-8 -*-

/**
 * Allows users to disable automatic redirects in their profile.
 */
class Mlp_Redirect_User_Settings {

	/**
	 * Initializes the objects.
	 *
	 * @return void
	 */
	public function setup() {

		$meta_key = 'mlp_redirect';

		$nonce = Mlp_Nonce_Validator_Factory::create( 'save_redirect_user_setting' );

		$user_settings_controller = new Mlp_User_Settings_Controller(
			new Mlp_Redirect_User_Settings_Html( $meta_key, $nonce ),
			new Mlp_User_Settings_Updater( $meta_key, $nonce )
		);
		$user_settings_controller->setup();

		$redirect_filter = new Mlp_Redirect_Filter( $meta_key );
		add_filter( 'mlp_do_redirect', array( $redirect_filter, 'filter_redirect' ) );
	}
}
