<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all API objects.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class APIServiceProvider implements ServiceProvider {

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

		$container->share( 'multilingualpress.content_relations', function ( Container $container ) {

			return new WPDBContentRelations(
				$container['multilingualpress.wpdb'],
				$container['multilingualpress.content_relations_table'],
				$container['multilingualpress.site_relations']
			);
		} );

		$container->share( 'multilingualpress.languages', function ( Container $container ) {

			return new WPDBLanguages(
				$container['multilingualpress.wpdb'],
				$container['multilingualpress.languages_table'],
				$container['multilingualpress.site_settings_repository']
			);
		} );

		$container->share( 'multilingualpress.site_relations', function ( Container $container ) {

			return new WPDBSiteRelations(
				$container['multilingualpress.wpdb'],
				$container['multilingualpress.site_relations_table']
			);
		} );

		$container->share( 'multilingualpress.translations', function ( Container $container ) {

			return new CachingTranslations(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.languages'],
				$container['multilingualpress.request'],
				$container['multilingualpress.type_factory']
			);
		} );
	}
}
