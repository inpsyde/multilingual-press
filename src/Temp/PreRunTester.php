<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Temp;

use Inpsyde\MultilingualPress\Service\Container;
use Mlp_Network_Plugin_Deactivation;
use Mlp_Self_Check;
use Mlp_Site_Relations;
use Mlp_Update_Plugin_Data;

/**
 * TEMPORARY (god) class to simplify the main plugin file.
 *
 * TODO: Refactor in the course of the Installation namespace.
 *
 * @package Inpsyde\MultilingualPress\Temp
 */
class PreRunTester {

	/**
	 * @param Container $container
	 *
	 * @return bool
	 */
	public function test( Container $container ) {

		global $pagenow, $wp_version, $wpdb;

		$properties = $container['multilingualpress.properties'];

		$type_factory = $container['multilingualpress.type_factory'];

		$self_check = new Mlp_Self_Check( $properties->plugin_file_path(), $pagenow, $type_factory );

		$requirements_check = $self_check->pre_install_check(
			$properties->plugin_name(),
			$properties->plugin_base_name(),
			$wp_version
		);

		if ( Mlp_Self_Check::PLUGIN_DEACTIVATED === $requirements_check ) {
			return false;
		}

		if ( Mlp_Self_Check::INSTALLATION_CONTEXT_OK === $requirements_check ) {
			$last_version = $type_factory->create_version_number( [
				get_site_option( 'mlp_version' ),
			] );

			$current_version = $type_factory->create_version_number( [
				$properties->version(),
			] );

			// TODO: Get off container, as soon as the API namespace has been refactored.
			$site_relations = new Mlp_Site_Relations( 'mlp_site_relations' );

			switch ( $self_check->is_current_version( $current_version, $last_version ) ) {
				case Mlp_Self_Check::NEEDS_INSTALLATION:
					( new Mlp_Update_Plugin_Data(
						$wpdb,
						$current_version,
						$last_version,
						$site_relations
					) )->install_plugin();
					break;

				case Mlp_Self_Check::NEEDS_UPGRADE:
					( new Mlp_Update_Plugin_Data(
						$wpdb,
						$current_version,
						$last_version,
						$site_relations
					) )->update( new Mlp_Network_Plugin_Deactivation() );
					break;
			}
		}

		return true;
	}
}
