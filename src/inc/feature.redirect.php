<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\MultilingualPress;

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_redirect' );

/**
 * Initializes the redirect controller.
 * @return void
 */
function mlp_feature_redirect() {

	( new Mlp_Redirect(
		MultilingualPress::resolve( 'multilingualpress.module_manager' ),
		MultilingualPress::resolve( 'multilingualpress.translations' )
	) )->setup();
}
