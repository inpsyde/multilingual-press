<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_translation_metabox' );

/**
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_translation_metabox( Inpsyde_Property_List_Interface $data ) {

	new Mlp_Translation_Metabox( $data );

	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	$switcher = new Mlp_Global_Switcher( Mlp_Global_Switcher::TYPE_POST );

	add_action( 'mlp_before_post_synchronization', array( $switcher, 'strip' ) );
	add_action( 'mlp_after_post_synchronization',  array( $switcher, 'fill' ) );
}
