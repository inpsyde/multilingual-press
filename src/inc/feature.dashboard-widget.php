<?php # -*- coding: utf-8 -*-
add_action( 'mlp_and_wp_loaded', 'mlp_feature_dashboard_widget' );
/**
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return void
 */
function mlp_feature_dashboard_widget( Inpsyde_Property_List_Interface $data ) {

	$controller = new Mlp_Dashboard_Widget( $data->get( 'site_relations' ) );
	$controller->initialize();
}
