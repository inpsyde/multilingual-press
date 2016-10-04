<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

/**
 * Deactivates plugins network-wide by matching (partial) base names against all active plugins.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
final class MatchingNetworkPluginDeactivator implements NetworkPluginDeactivator {

	/**
	 * Deactivates the given plugins network-wide.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $plugins Plugin base names (or partials). These will be matched against all active plugins.
	 *
	 * @return string[] An array with all plugins that were deactivated.
	 */
	public function deactivate_plugins( array $plugins ) {

		$active_plugins = (array) get_network_option( null, NetworkPluginDeactivator::OPTION, [] );

		$plugins_to_deactivate = $this->get_plugins_to_deactivate( array_keys( $active_plugins ), $plugins );
		if ( ! $plugins_to_deactivate ) {
			return $plugins_to_deactivate;
		}

		$active_plugins = array_diff_key( $active_plugins, array_flip( $plugins_to_deactivate ) );

		update_site_option( NetworkPluginDeactivator::OPTION, $active_plugins );

		return $plugins_to_deactivate;
	}

	/**
	 * Returns the base names of plugins that are to be deactivated.
	 *
	 * @param string[] $active_plugins Active plugin base names.
	 * @param string[] $plugins        Plugins to search for.
	 *
	 * @return array The base names of plugins that are to be deactivated.
	 */
	private function get_plugins_to_deactivate( array $active_plugins, array $plugins ) {

		return array_filter( $active_plugins, function ( $active_plugin ) use ( $plugins ) {

			foreach ( $plugins as $plugin ) {
				if ( false !== strpos( $active_plugin, $plugin ) ) {
					return true;
				}
			}

			return false;
		} );
	}
}
