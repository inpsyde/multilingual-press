<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingual-press/
 * Description: Create a fast translation network on WordPress multisite. Run each language in a separate site, and connect the content in a lightweight user interface. Use a customizable widget to link to all sites.
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com
 * Version:     2.9.0
 * Text Domain: multilingual-press
 * Domain Path: /src/languages
 * License:     GPLv3
 * Network:     true
 */

defined( 'ABSPATH' ) || die();

define( 'MLP_PLUGIN_FILE', __FILE__ );

require dirname( __FILE__ ) . '/src/multilingual-press.php';
