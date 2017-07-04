<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Relations;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUIRegistry;
use Inpsyde\MultilingualPress\Relations\Post\RelationshipController;
use Inpsyde\MultilingualPress\Relations\Post\RelationshipControlView;
use Inpsyde\MultilingualPress\Relations\Post\RelationshipPermission as PostRelationshipPermission;
use Inpsyde\MultilingualPress\Relations\Post\Search\RequestAwareSearch;
use Inpsyde\MultilingualPress\Relations\Post\Search\SearchController;
use Inpsyde\MultilingualPress\Relations\Post\Search\StatusAwareSearchResultsView;
use Inpsyde\MultilingualPress\Relations\Term\RelationshipPermission as TermRelationshipPermission;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI\AdvancedPostTranslator;

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

		$this->register_post_translation( $container );

		$this->register_term_translation( $container );
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
			$this->bootstrap_post_translation( $container );
		}
	}

	/**
	 * Registers the post translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_post_translation( Container $container ) {

		$container['multilingualpress.post_relationship_control_search'] = function ( Container $container ) {

			return new RequestAwareSearch(
				$container['multilingualpress.server_request']
			);
		};

		$container['multilingualpress.post_relationship_control_search_controller'] = function ( Container $container ) {

			return new SearchController(
				$container['multilingualpress.post_relationship_control_search_results']
			);
		};

		$container['multilingualpress.post_relationship_control_search_results'] = function ( Container $container ) {

			return new StatusAwareSearchResultsView(
				$container['multilingualpress.post_relationship_control_search']
			);
		};

		$container['multilingualpress.post_relationship_control_view'] = function ( Container $container ) {

			return new RelationshipControlView(
				$container['multilingualpress.post_relationship_control_search_results'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.post_relationship_controller'] = function ( Container $container ) {

			return new RelationshipController(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.server_request']
			);
		};

		$container['multilingualpress.post_relationship_permission'] = function ( Container $container ) {

			return new PostRelationshipPermission(
				$container['multilingualpress.content_relations']
			);
		};
	}

	/**
	 * Registers the term translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_term_translation( Container $container ) {

		$container['multilingualpress.term_relationship_permission'] = function ( Container $container ) {

			return new TermRelationshipPermission(
				$container['multilingualpress.content_relations']
			);
		};
	}

	/**
	 * Bootstraps the post translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_post_translation( Container $container ) {

		$relationship_control_view = $container['multilingualpress.post_relationship_control_view'];

		add_action( MetaBoxUIRegistry::ACTION_UI_SELECTED, function ( MetaBoxUI $selected_ui ) use (
			$relationship_control_view
		) {

			if ( $selected_ui instanceof AdvancedPostTranslator ) {
				$relationship_control_view->register();
			}
		} );

		$server_request = $container['multilingualpress.server_request'];

		$action = $server_request->body_value( 'action', INPUT_REQUEST, FILTER_SANITIZE_STRING );
		if ( is_string( $action ) && '' !== $action && wp_doing_ajax() ) {
			switch ( $action ) {
				case SearchController::ACTION:
					$container['multilingualpress.post_relationship_control_search_controller']->initialize(
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
