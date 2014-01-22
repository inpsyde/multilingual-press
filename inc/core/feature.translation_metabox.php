<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_translation_metabox' );

function mlp_feature_translation_metabox( Inpsyde_Property_List_Interface $data ) {

	$box = new Mlp_Translation_Metabox( $data );
}