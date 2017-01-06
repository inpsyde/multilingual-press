<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\MultilingualPress;

if ( is_admin() ) {
	add_action( 'wp_loaded', 'mlp_feature_term_translator', 0 );
}

/**
 * @return bool
 */
function mlp_feature_term_translator() {

	$taxonomy = empty( $_REQUEST['taxonomy'] ) ? '' : (string) $_REQUEST['taxonomy'];

	$term_taxonomy_id = empty( $_REQUEST['tag_ID'] ) ? 0 : (int) $_REQUEST['tag_ID'];

	$controller = new Mlp_Term_Translation_Controller(
		MultilingualPress::resolve( 'multilingualpress.content_relations' ),
		new WPNonce( "save_{$taxonomy}_translations_$term_taxonomy_id" )
	);

	return $controller->setup();
}
