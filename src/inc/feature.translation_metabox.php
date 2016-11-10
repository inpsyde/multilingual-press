<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Translation\FullRequestDataManipulator;
use Inpsyde\MultilingualPress\Translation\RequestDataManipulator;

add_action( 'inpsyde_mlp_loaded', 'mlp_feature_translation_metabox' );

/**
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_translation_metabox( Inpsyde_Property_List_Interface $data ) {

	new Mlp_Translation_Metabox( $data );

	if ( 'POST' !== $_SERVER[ 'REQUEST_METHOD' ] )
		return;

	// TODO: Fetch the manipulator off the container, but somehow take care of not adding it multiple times!
	$request_data_manipulator = new FullRequestDataManipulator( RequestDataManipulator::METHOD_POST );
	add_action( 'mlp_before_post_synchronization', [ $request_data_manipulator, 'clear_data' ] );
	add_action( 'mlp_after_post_synchronization', [ $request_data_manipulator, 'restore_data' ] );
}
