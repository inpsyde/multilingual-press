<?php # -*- coding: utf-8 -*-
/**
 * Uninstall routines.
 *
 * This file is called automatically when the plugin is deleted per user interface.
 *
 * @see https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Installation\Uninstaller;

defined( 'ABSPATH' ) or die();

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

if ( ! is_multisite() ) {
	return;
}

$main_plugin_file = __DIR__ . '/multilingual-press.php';

if (
	plugin_basename( $main_plugin_file ) !== WP_UNINSTALL_PLUGIN
	|| ! is_readable( $main_plugin_file )
) {
	unset( $main_plugin_file );

	return;
}

/** @noinspection PhpIncludeInspection
 * MultilingualPress main plugin file.
 */
require_once $main_plugin_file;

unset( $main_plugin_file );

if ( bootstrap() ) {
	return;
}

$uninstaller = resolve( 'multilingualpress.uninstaller', Uninstaller::class );

$uninstaller->uninstall_tables( [
	resolve( 'multilingualpress.content_relations_table', Table::class ),
	resolve( 'multilingualpress.languages_table', Table::class ),
	resolve( 'multilingualpress.site_relations_table', Table::class ),
] );

// TODO: Use class constants instead of hard-coded strings.
$uninstaller->delete_network_options( [
	SiteSettingsRepository::OPTION_SETTINGS,
	'inpsyde_multilingual_cpt',
	'inpsyde_multilingual_quicklink_options',
	// Currently defined in a private property on ~\MultilingualPress.
	'mlp_version',
	// Currently defined in ~\Core\CoreServiceProvider.
	'multilingualpress_modules',
	'multilingual_press_check_db',
	// Old option, replaced by 'multilingualpress_modules'.
	'state_modules',
] );

// TODO: Use class constants instead of hard-coded strings.
$uninstaller->delete_site_options( [
	'inpsyde_license_status_MultilingualPress Pro',
	'inpsyde_multilingual_blog_relationship',
	'inpsyde_multilingual_default_actions',
	'inpsyde_multilingual_flag_url',
	'inpsyde_multilingual_redirect',
] );

unset( $uninstaller );
