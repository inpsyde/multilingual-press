<?php
/**
 * ${CARET}
 *
 * @version 2014.09.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */


class Mlp_Network_Plugin_Deactivation implements Mlp_Network_Plugin_Deactivation_Interface {

	/**
	 * Site option to check;
	 *
	 * @var string
	 */
	private $option_name = 'active_sitewide_plugins';

	/**
	 * Searches in active network plugins.
	 *
	 * It will find even partial matches, so you can pass a directory name, and
	 * it will find the files in that directory. The search is case-sensitive.
	 *
	 * @param  array $plugins  List of plugin base names. See plugin_basename().
	 * @return array           All matches that were removed.
	 */
	public function deactivate( array $plugins ) {

		$active_plugins = get_site_option( $this->option_name, array() );
		$files          = array_keys( $active_plugins );
		$remove         = $this->get_plugins_to_deactivate( $files, $plugins );

		if ( empty( $remove ) ) {
			return $remove;
		}

		$active_plugins = $this->remove_plugins( $active_plugins, $remove );

		update_site_option( $this->option_name, $active_plugins );

		return array_keys( $remove );
	}

	/**
	 * @param  array $files   List of active plugin base names.
	 * @param  array $plugins List of plugins to search for.
	 * @return array          List of base names to remove.
	 *                        The name is the key, the value is always 1.
	 */
	private function get_plugins_to_deactivate( array $files, array $plugins ) {

		$return = array();

		foreach ( $files as $file ) {

			foreach ( $plugins as $plugin ) {

				if ( false !== strpos( $file, $plugin ) ) {
					$return[ $file ] = 1;
				}
			}
		}

		return $return;
	}

	/**
	 * Unset plugin that must be deactivated.
	 *
	 * @param  array $active_plugins
	 * @param  array $to_remove
	 * @return array
	 */
	private function remove_plugins( array $active_plugins, array $to_remove ) {

		$files = array_keys( $active_plugins );

		foreach ( $files as $file ) {
			if ( isset( $to_remove[ $file ] ) ) {
				unset( $active_plugins[ $file ] );
			}
		}

		return $active_plugins;
	}
}
