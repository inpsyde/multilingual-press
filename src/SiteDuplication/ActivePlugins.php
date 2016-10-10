<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

/**
 * Handles (de)activation of all active plugins.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
class ActivePlugins {

	/**
	 * Name of the option that stores all active plugins.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'active_plugins';

	/**
	 * Fires the plugin activation hooks for all active plugins.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of plugins activated.
	 */
	public function activate() {

		$plugins = get_option( self::OPTION );
		if ( ! $plugins ) {
			return 0;
		}

		array_walk( $plugins, function ( $plugin ) {

			/** This action is documented in wp-admin/includes/plugin.php. */
			do_action( 'activate_plugin', $plugin, false );

			/** This action is documented in wp-admin/includes/plugin.php. */
			do_action( "activate_$plugin", false );

			/** This action is documented in wp-admin/includes/plugin.php. */
			do_action( 'activated_plugin', $plugin, false );
		} );

		return count( $plugins );
	}

	/**
	 * Deactivates all plugins.
	 *
	 * @since  3.0.0
	 *
	 * @retuvn bool Whether or not all plugins were deactivated successfully.
	 */
	public function deactivate() {

		return update_option( self::OPTION, [] );
	}
}
