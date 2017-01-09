<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all Installation objects.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
final class InstallationServiceProvider implements ServiceProvider  {

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
				$container['multilingualpress.site_relations_checker']
			);
		};

		$container['multilingualpress.updater'] = function ( Container $container ) {

			return new Updater(
				$container['multilingualpress.table_installer'],
				$container['multilingualpress.content_relations_table'],
				$container['multilingualpress.languages_table'],
				$container['multilingualpress.site_relations_table'],
				$container['multilingualpress.site_relations']
			);
		};
	}
}
