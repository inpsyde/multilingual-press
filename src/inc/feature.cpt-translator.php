<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_cpt_translator', 8 );

/**
 * Init the CPT filter routine.
 *
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_cpt_translator( Inpsyde_Property_List_Interface $data ) {

	new Mlp_Cpt_Translator( $data, new WPNonce( 'save_cpt_translator_settings' ) );
}
