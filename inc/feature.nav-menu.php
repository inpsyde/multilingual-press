<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_nav_menu_init' );

/**
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_nav_menu_init( Inpsyde_Property_List_Interface $data ) {

	$controller = new Mlp_Nav_Menu_Controller(
		$data->language_api,
		$data->assets
	);

	if ( is_admin() )
		$controller->backend_setup( $data->js_url );
	else
		add_action( 'template_redirect', array ( $controller, 'frontend_setup' ) );
}

