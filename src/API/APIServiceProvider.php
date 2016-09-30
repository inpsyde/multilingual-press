<?php # -*- coding: utf-8 -*-

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
				$container['multilingualpress.content_relations_table'],
				$container['multilingualpress.site_relations']
			);
		} );

		$container->share( 'multilingualpress.site_relations', function ( Container $container ) {

			return new WPDBSiteRelations( $container['multilingualpress.site_relations_table'] );
		} );
	}
}
