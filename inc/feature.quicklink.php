<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_quicklink' );

/**
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_quicklink( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Quicklink(
		$data->module_manager,
		$data->language_api,
		$data->assets
	);
}