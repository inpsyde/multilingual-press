<?php # -*- coding: utf-8 -*-
add_action( 'mlp_and_wp_loaded', 'mlp_feature_trasher' );

/**
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return void
 */
function mlp_feature_trasher( Inpsyde_Property_List_Interface $data ) {

	$controller = new Mlp_Trasher( $data->get( 'module_manager' ) );
	$controller->initialize();
}
