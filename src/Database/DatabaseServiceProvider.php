<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all database objects.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
final class DatabaseServiceProvider implements ServiceProvider {

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

		$container->share( 'multilingualpress.table.content_relations', function () {

			return new Table\ContentRelations( $GLOBALS['wpdb']->base_prefix );
		} );

		$container->share( 'multilingualpress.table.languages', function () {

			return new Table\Languages( $GLOBALS['wpdb']->base_prefix );
		} );

		$container->share( 'multilingualpress.table.site_relations', function () {

			return new Table\SiteRelations( $GLOBALS['wpdb']->base_prefix );
		} );

		$container->share( 'multilingualpress.table_duplicator', function () {

			return new WPDBTableDuplicator();
		} );

		$container->share( 'multilingualpress.table_installer', function () {

			return new WPDBTableInstaller();
		} );

		$container->share( 'multilingualpress.table_list', function () {

			return new WPDBTableList();
		} );

		$container->share( 'multilingualpress.table_replacer', function () {

			return new WPDBTableReplacer();
		} );

		$container->share( 'multilingualpress.table_string_replacer', function () {

			return new WPDBTableStringReplacer();
		} );
	}
}
