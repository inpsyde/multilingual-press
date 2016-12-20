<?php # -*- coding: utf-8 -*-

if ( is_admin() ) {
	global $wp_version;

	if ( version_compare( $wp_version, '4.7', '<' ) ) {
		add_action( 'inpsyde_mlp_loaded', 'mlp_feature_user_backend_language', 0 );
	}
}

/**
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return void
 */
function mlp_feature_user_backend_language( Inpsyde_Property_List_Interface $data ) {

	$user_lang = new Mlp_User_Backend_Language( $data->get( 'module_manager' ) );
	$user_lang->setup();
}
