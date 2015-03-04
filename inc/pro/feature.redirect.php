<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_redirect' );

/**
 * Initialize the redirect controller.
 *
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_redirect( Inpsyde_Property_List_Interface $data ) {

	$redirect = new Mlp_Redirect(
		$data->module_manager,
		$data->language_api,
		"$data->image_url/check.png"
	);
	$redirect->setup();

	$user = new Mlp_Redirect_User_Settings;
	$user->setup();
}