<?php
/**
 * Allow users to disable automatic redirects in their profile.
 *
 * @version 2014.07.05
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Redirect_User_Settings {

	/**
	 * @var string
	 */
	private $key = 'mlp_redirect';

	/**
	 * Initialize the objects.
	 *
	 * @return void
	 */
	public function setup() {

		$nonce      = new Inpsyde_Nonce_Validator( $this->key );
		$view       = new Mlp_Redirect_User_Settings_Html( $this->key, $nonce );
		$updater    = new Mlp_User_Settings_Updater( $this->key, $nonce );
		$controller = new Mlp_User_Settings_Controller( $view, $updater );
		$controller->setup();

		add_filter( 'mlp_do_redirect', array ( $this, 'intercept_redirect' ) );
	}

	/**
	 * Stop redirect when the user has turned it off.
	 *
	 * This needs a better place.
	 *
	 * @param  bool $bool
	 * @return bool
	 */
	public function intercept_redirect( $bool ) {

		$user = wp_get_current_user();

		if ( ! is_a( $user, 'WP_User' ) )
			return $bool;

		$current = (int) get_user_meta( $user->ID, $this->key );

		return 1 === $current ? FALSE : $bool;
	}
}