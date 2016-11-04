<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSetting;
use Inpsyde\MultilingualPress\Common\Setting\User\SecureUserSettingUpdater;

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

		$nonce = new WPNonce( 'save_redirect_user_setting' );

		( new UserSetting(
			new Mlp_Redirect_User_Settings_Html( $meta_key, $nonce ),
			new SecureUserSettingUpdater( $meta_key, $nonce )
		) )->register();

		add_filter( 'mlp_do_redirect', [ new Mlp_Redirect_Filter( $meta_key ), 'filter_redirect' ] );
	}
}
