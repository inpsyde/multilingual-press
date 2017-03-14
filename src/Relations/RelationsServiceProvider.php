<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Relations;

use Inpsyde\MultilingualPress\Relations\Post\RelationshipContext;
use Inpsyde\MultilingualPress\Relations\Post\RelationshipController;
use Inpsyde\MultilingualPress\Relations\Post\RelationshipControlView;
use Inpsyde\MultilingualPress\Relations\Post\Search\RequestAwareSearch;
use Inpsyde\MultilingualPress\Relations\Post\Search\SearchController;
use Inpsyde\MultilingualPress\Relations\Post\Search\StatusAwareSearchResultsView;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use WP_Post;

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

		$container['multilingualpress.relationship_control_search'] = function () {

			return new RequestAwareSearch();
		};

		$container['multilingualpress.relationship_control_search_controller'] = function ( Container $container ) {

			return new SearchController(
				$container['multilingualpress.relationship_control_search_results_view']
			);
		};

		$container['multilingualpress.relationship_control_search_results_view'] = function ( Container $container ) {

			return new StatusAwareSearchResultsView(
				$container['multilingualpress.relationship_control_search']
			);
		};

		$container['multilingualpress.relationship_control_view'] = function ( Container $container ) {

			return new RelationshipControlView(
				$container['multilingualpress.relationship_control_search_results_view'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.relationship_controller'] = function ( Container $container ) {

			return new RelationshipController(
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
			$relationship_control_view = $container['multilingualpress.relationship_control_view'];

			add_action(
				'mlp_translation_meta_box_bottom',
				function ( WP_Post $post, $remote_site_id, WP_Post $remote_post ) use ( $relationship_control_view ) {

					global $pagenow;
					if ( 'post.php' === $pagenow ) {
						$relationship_control_view->render( new RelationshipContext( [
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

			if ( wp_doing_ajax() && ! empty( $_REQUEST['action'] ) ) {
				switch ( $_REQUEST['action'] ) {
					case SearchController::ACTION:
						$container['multilingualpress.relationship_control_search_controller']->initialize();
						break;

					case RelationshipController::ACTION_CONNECT_EXISTING:
					case RelationshipController::ACTION_CONNECT_NEW:
					case RelationshipController::ACTION_DISCONNECT:
						$container['multilingualpress.relationship_controller']->initialize();
						break;
				}
			}
		}
	}
}
