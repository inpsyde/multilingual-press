<?php # -*- coding: utf-8 -*-

if ( is_admin() ) {
	add_action( 'mlp_and_wp_loaded', 'mlp_feature_term_translator', 1000 );
}

/**
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return bool
 */
function mlp_feature_term_translator( Inpsyde_Property_List_Interface $data ) {

	$controller = new Mlp_Term_Translation_Controller(
		$data->get( 'content_relations' ),
		$data->get( 'assets' )
	);

	return $controller->setup();
}
