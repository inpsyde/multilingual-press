<?php # -*- coding: utf-8 -*-

is_admin() && add_action( 'mlp_and_wp_loaded', 'mlp_feature_relationship_control' );

/**
 * Init the relinking feature.
 *
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_relationship_control( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Relationship_Control( $data );

	if ( 'POST' !== $_SERVER[ 'REQUEST_METHOD' ] )
		return;

	$switcher = new Mlp_Global_Switcher( Mlp_Global_Switcher::TYPE_POST );

	add_action( 'mlp_before_post_synchronization', array ( $switcher, 'strip' ) );
	add_action( 'mlp_after_post_synchronization',  array ( $switcher, 'fill' ) );
}
