<?php # -*- coding: utf-8 -*-
/**
 * Uninstall routines.
 *
 * This file is called automatically when the plugin is deleted per user interface.
 *
 * @see https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Installation\Uninstaller;

defined( 'ABSPATH' ) || die();

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

if ( ! is_multisite() ) {
	return;
}

$main_plugin_file = __DIR__ . '/multilingualpress.php';

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
	Activation\NetworkOptionActivator::OPTION,
	Core\Admin\SiteSettingsRepository::OPTION_SETTINGS,
	Installation\NetworkPluginDeactivator::OPTION,
	Module\CustomPostTypeSupport\PostTypeRepository::OPTION,
	Module\Quicklinks\SettingsRepository::OPTION,
	MultilingualPress::OPTION_VERSION,
	// Currently defined in ~\Core\CoreServiceProvider.
	'multilingualpress_modules',
] );

// TODO: Use class constants instead of hard-coded strings.
$uninstaller->delete_site_options( [
	Module\Redirect\SettingsRepository::OPTION_SITE
] );

unset( $uninstaller );
