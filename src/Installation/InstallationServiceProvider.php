<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all Installation objects.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
final class InstallationServiceProvider implements BootstrappableServiceProvider {

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container['multilingualpress.installation_checker'] = function ( Container $container ) {

			return new InstallationChecker(
				$container['multilingualpress.system_checker'],
				$container['multilingualpress.properties'],
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.installer'] = function ( Container $container ) {

			return new Installer(
				$container['multilingualpress.table_installer'],
				$container['multilingualpress.content_relations_table'],
				$container['multilingualpress.languages_table'],
				$container['multilingualpress.site_relations_table']
			);
		};

		$container->share( 'multilingualpress.network_plugin_deactivator', function () {

			return new MatchingNetworkPluginDeactivator();
		} );

		$container['multilingualpress.site_relations_checker'] = function ( Container $container ) {

			return new ContextAwareSiteRelationsChecker(
				$container['multilingualpress.site_relations']
			);
		};

		$container['multilingualpress.system_checker'] = function ( Container $container ) {

			return new SystemChecker(
				$container['multilingualpress.properties'],
				$container['multilingualpress.type_factory'],
				$container['multilingualpress.site_relations_checker'],
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container->share( 'multilingualpress.uninstaller', function ( Container $container ) {

			return new Uninstaller(
				$container['multilingualpress.table_installer']
			);
		} );

		$container['multilingualpress.updater'] = function ( Container $container ) {

			return new Updater(
				$container['multilingualpress.wpdb'],
				$container['multilingualpress.site_settings_repository'],
				$container['multilingualpress.table_installer'],
				$container['multilingualpress.content_relations_table'],
				$container['multilingualpress.languages_table'],
				$container['multilingualpress.site_relations_table'],
				$container['multilingualpress.site_relations']
			);
		};
	}

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		add_action( SystemChecker::ACTION_CHECKED_VERSION, function (
			int $result,
			VersionNumber $installed_version
		) use ( $container ) {

			if ( did_action( SystemChecker::ACTION_CHECKED_VERSION ) > 1 ) {
				return;
			}

			remove_all_actions( SystemChecker::ACTION_CHECKED_VERSION );

			switch ( $result ) {
				case SystemChecker::NEEDS_INSTALLATION:
					$container['multilingualpress.installer']->install();
					break;

				case SystemChecker::NEEDS_UPGRADE:
					$container['multilingualpress.network_plugin_deactivator']->deactivate_plugins( [
						'disable-acf.php',
						'mlp-wp-seo-compat.php',
					] );

					$container['multilingualpress.updater']->update( $installed_version );
					break;
			}
		} );
	}
}
