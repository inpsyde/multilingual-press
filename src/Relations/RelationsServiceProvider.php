<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Relations;

use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all relations objects.
 *
 * @package Inpsyde\MultilingualPress\Relations
 * @since   3.0.0
 */
final class RelationsServiceProvider implements BootstrappableServiceProvider {

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

		$container['multilingualpress.relationship_control_search'] = function ( Container $container ) {

			return new Post\Search\RequestAwareSearch( $container['multilingualpress.server_request'] );
		};

		$container['multilingualpress.relationship_control_search_controller'] = function ( Container $container ) {

			return new Post\Search\SearchController(
				$container['multilingualpress.post_relationship_control_search_results_view']
			);
		};

		$container['multilingualpress.post_relationship_control_search_results_view'] = function ( Container $container ) {

			return new Post\Search\StatusAwareSearchResultsView(
				$container['multilingualpress.relationship_control_search']
			);
		};

		$container['multilingualpress.post_relationship_control_view'] = function ( Container $container ) {

			return new Post\RelationshipControlView(
				$container['multilingualpress.post_relationship_control_search_results_view'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.post_relationship_controller'] = function ( Container $container ) {

			return new Post\RelationshipController(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.server_request']
			);
		};

		$container['multilingualpress.post_relationship_permission'] = function ( Container $container ) {

			return new Post\RelationshipPermission(
				$container['multilingualpress.content_relations']
			);
		};

		$container['multilingualpress.term_relationship_permission'] = function ( Container $container ) {

			return new Term\RelationshipPermission(
				$container['multilingualpress.content_relations']
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

		if ( is_admin() ) {
			$post_relationship_control_view = $container['multilingualpress.post_relationship_control_view'];

			add_action(
				'mlp_translation_meta_box_bottom',
				function ( \WP_Post $post, $remote_site_id, \WP_Post $remote_post ) use ( $post_relationship_control_view ) {

					global $pagenow;
					if ( 'post.php' === $pagenow ) {
						$post_relationship_control_view->render( new RelationshipContext( [
							RelationshipContext::KEY_REMOTE_POST_ID => $remote_post->ID,
							RelationshipContext::KEY_REMOTE_SITE_ID => $remote_site_id,
							RelationshipContext::KEY_SOURCE_POST_ID => $post->ID,
							RelationshipContext::KEY_SOURCE_SITE_ID => get_current_blog_id(),
						] ) );
					}
				},
				200,
				3
			);

			$server_request = $container['multilingualpress.server_request'];

			$action = $server_request->body_value( 'action' );
			if ( $action && wp_doing_ajax() ) {
				switch ( $action ) {
					case SearchController::ACTION:
						$container['multilingualpress.relationship_control_search_controller']->initialize(
							$server_request
						);
						break;

					case RelationshipController::ACTION_CONNECT_EXISTING:
					case RelationshipController::ACTION_CONNECT_NEW:
					case RelationshipController::ACTION_DISCONNECT:
						$container['multilingualpress.post_relationship_controller']->initialize();
						break;
				}
			}
		}
	}
}
