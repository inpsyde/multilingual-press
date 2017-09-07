<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_cpt_translator', 8 );

/**
 * Init the CPT filter routine.
 *
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_cpt_translator( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Cpt_Translator( $data );
}
