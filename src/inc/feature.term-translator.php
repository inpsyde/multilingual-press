<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;

if ( is_admin() ) {
	add_action( 'mlp_and_wp_loaded', 'mlp_feature_term_translator', 1000 );
}

/**
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return bool
 */
function mlp_feature_term_translator( Inpsyde_Property_List_Interface $data ) {

	$taxonomy = empty( $_REQUEST['taxonomy'] ) ? '' : (string) $_REQUEST['taxonomy'];

	$term_taxonomy_id = empty( $_REQUEST['tag_ID'] ) ? 0 : (int) $_REQUEST['tag_ID'];

	$controller = new Mlp_Term_Translation_Controller(
		$data->get( 'content_relations' ),
		new WPNonce( "save_{$taxonomy}_translations_$term_taxonomy_id" )
	);

	return $controller->setup();
}
