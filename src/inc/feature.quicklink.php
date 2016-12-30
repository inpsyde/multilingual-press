<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_quicklink' );

/**
 * @param Inpsyde_Property_List_Interface $data Plugin data.
 *
 * @return void
 */
function mlp_feature_quicklink( Inpsyde_Property_List_Interface $data ) {

	$controller = new Mlp_Quicklink(
		$data->get( 'module_manager' ),
		$data->get( 'translations' ),
		$data->get( 'assets' ),
		new WPNonce( 'save_quicklink_position' )
	);
	$controller->initialize();
}
