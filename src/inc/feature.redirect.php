<?php # -*- coding: utf-8 -*-

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_redirect' );

/**
 * Initializes the redirect controller.
 *
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return void
 */
function mlp_feature_redirect( Inpsyde_Property_List_Interface $data ) {

	$redirect = new Mlp_Redirect(
		$data->get( 'module_manager' ),
		$data->get( 'language_api' ),
		null,
		$data->get( 'locations' )
	);
	$redirect->setup();
}
