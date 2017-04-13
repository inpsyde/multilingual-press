<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

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

		$this->register_tables( $container );

		$container->share( 'multilingualpress.table_duplicator', function ( Container $container ) {

			return new WPDBTableDuplicator(
				$container['multilingualpress.wpdb']
			);
		} );

		$container->share( 'multilingualpress.table_installer', function ( Container $container ) {

			return new WPDBTableInstaller(
				$container['multilingualpress.wpdb']
			);
		} );

		$container->share( 'multilingualpress.table_list', function ( Container $container ) {

			return new WPDBTableList(
				$container['multilingualpress.wpdb']
			);
		} );

		$container->share( 'multilingualpress.table_replacer', function ( Container $container ) {

			return new WPDBTableReplacer(
				$container['multilingualpress.wpdb']
			);
		} );

		$container->share( 'multilingualpress.table_string_replacer', function ( Container $container ) {

			return new WPDBTableStringReplacer(
				$container['multilingualpress.wpdb']
			);
		} );

		$container->share( 'multilingualpress.wpdb', function () {

			return $GLOBALS['wpdb'];
		} );
	}

	/**
	 * Registers the tables.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_tables( Container $container ) {

		$container->share( 'multilingualpress.content_relations_table', function ( Container $container ) {

			return new Table\ContentRelationsTable( $container['multilingualpress.wpdb']->base_prefix );
		} );

		$container->share( 'multilingualpress.languages_table', function ( Container $container ) {

			return new Table\LanguagesTable( $container['multilingualpress.wpdb']->base_prefix );
		} );

		$container->share( 'multilingualpress.site_relations_table', function ( Container $container ) {

			return new Table\SiteRelationsTable( $container['multilingualpress.wpdb']->base_prefix );
		} );
	}
}
