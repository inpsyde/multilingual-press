<?php # -*- coding: utf-8 -*-

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_alternative_language_title' );

/**
 * Sets up the feature.
 *
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return void
 */
function mlp_feature_alternative_language_title( Inpsyde_Property_List_Interface $data ) {

	$controller = new Mlp_Alternative_Language_Title(
		new Mlp_Alternative_Language_Title_Module( $data->get( 'module_manager' ) ),
		new Mlp_Admin_Bar_Customizer()
	);
	$controller->setup();
}
