<?php # -*- coding: utf-8 -*-

// TODO: With WordPress 4.6 + 2, replace the below code with this:
/*
add_action( 'inpsyde_mlp_init', 'mlp_widget_setup' );

function mlp_widget_setup( Inpsyde_Property_List_Interface $plugin_data ) {

	register_widget( new Mlp_Widget( $plugin_data->get( 'assets' ) ) );
}
*/

add_action( 'inpsyde_mlp_init', 'mlp_widget_setup' );

add_action( 'widgets_init', [ 'Mlp_Widget', 'widget_register' ] );

/**
 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data.
 *
 * @return void
 */
function mlp_widget_setup( Inpsyde_Property_List_Interface $plugin_data ) {

	Mlp_Widget::insert_asset_instance( $plugin_data->get( 'assets' ) );
}
