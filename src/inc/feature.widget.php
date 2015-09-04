<?php # -*- coding: utf-8 -*-

add_action( 'inpsyde_mlp_init', 'mlp_widget_setup' );

add_action( 'widgets_init', array( 'Mlp_Widget', 'widget_register' ) );

/**
 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data.
 *
 * @return void
 */
function mlp_widget_setup( Inpsyde_Property_List_Interface $plugin_data ) {

	Mlp_Widget::insert_asset_instance( $plugin_data->get( 'assets' ) );
}
