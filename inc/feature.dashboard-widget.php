<?php # -*- coding: utf-8 -*-
add_action( 'mlp_and_wp_loaded', 'mlp_feature_dashboard_widget' );
/**
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_dashboard_widget( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Dashboard_Widget( $data );
}