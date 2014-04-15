<?php
/**
 * Plugin Name: Multilingual Press Free
 * Plugin URI:  http://marketpress.com/product/multilingual-press-pro/?piwik_campaign=mlp&piwik_kwd=free
 * Description: Run WordPress Multisite with multiple languages.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     2.0.1
 * Text Domain: multilingualpress
 * Domain Path: /languages
 * License:     GPLv3
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Multilingual_Press' ) )
	require plugin_dir_path( __FILE__ ) . 'inc/Multilingual_Press.php';

// Kick-Off
add_action( 'plugins_loaded', 'mlp_init', 0 );

function mlp_init() {

	$path   = plugin_dir_path( __FILE__ );

	if ( ! class_exists( 'Mlp_Load_Controller' ) )
		require $path . 'inc/autoload/Mlp_Load_Controller.php';

	$loader = new Mlp_Load_Controller( $path . 'inc' );

	$data   = new Inpsyde_Property_List;

	$data->loader           = $loader->get_loader();
	$data->plugin_dir_path  = $path;
	$data->plugin_file_path = __FILE__;
	$data->plugin_base_name = plugin_basename( __FILE__ );
	$data->plugin_url       = plugins_url( '/', __FILE__ );
	$data->css_url          = "{$data->plugin_url}css/";
	$data->js_url           = "{$data->plugin_url}js/";
	$data->image_url        = "{$data->plugin_url}images/";

	$headers = get_file_data(
		__FILE__,
		array (
			'text_domain_path' => 'Domain Path',
			'plugin_uri'       => 'Plugin URI',
			'plugin_name'      => 'Plugin Name',
			'version'          => 'Version'
		)
	);

	foreach ( $headers as $name => $value )
		$data->$name = $value;

	new Multilingual_Press( $data );
}