<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_translation_metabox' );

/**
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_translation_metabox( Inpsyde_Property_List_Interface $data ) {

	new Mlp_Translation_Metabox( $data );
}