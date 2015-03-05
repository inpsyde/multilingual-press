<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_advanced_translator', 9 );

/**
 * Init the advanced translator.
 *
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_advanced_translator( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Advanced_Translator( $data );
}