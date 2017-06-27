<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUIRegistry;
use Inpsyde\MultilingualPress\Common\WordPressRequestContext;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all translation objects.
 *
 * @package Inpsyde\MultilingualPress\Translation
 * @since   3.0.0
 */
final class TranslationServiceProvider implements BootstrappableServiceProvider {

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

		$this->register_translators( $container );
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

		$this->bootstrap_post_translation( $container );

		$this->bootstrap_term_translation( $container );

		$this->bootstrap_translators( $container );
	}

	/**
	 * Registers the post translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_post_translation( Container $container ) {

		$container->share( 'multilingualpress.active_post_types', function () {

			return new Post\ActivePostTypes();
		} );

		$container['multilingualpress.post_meta_box_factory'] = function ( Container $container ) {

			return new Post\MetaBoxFactory(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.active_post_types']
			);
		};

		$container['multilingualpress.post_meta_box_registrar'] = function ( Container $container ) {

			return new Post\PostMetaBoxRegistrar(
				$container['multilingualpress.post_meta_box_factory'],
				$container['multilingualpress.post_relationship_permission'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.nonce_factory']
			);
		};

		$container['multilingualpress.post_translation_advanced_ui'] = function ( Container $container ) {

			return new Post\MetaBox\UI\AdvancedPostTranslator(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.asset_manager']
			);
		};

		$container['multilingualpress.post_translation_simple_ui'] = function ( Container $container ) {

			return new Post\MetaBox\UI\SimplePostTranslator(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.server_request']
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

		$container->share( 'multilingualpress.active_taxonomies', function ( Container $container ) {

			return new Term\ActiveTaxonomies(
				$container['multilingualpress.active_post_types']
			);
		} );

		$container['multilingualpress.term_meta_box_factory'] = function ( Container $container ) {

			return new Term\MetaBoxFactory(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.active_taxonomies']
			);
		};

		$container['multilingualpress.term_meta_box_registrar'] = function ( Container $container ) {

			return new Term\TermMetaBoxRegistrar(
				$container['multilingualpress.term_meta_box_factory'],
				$container['multilingualpress.term_relationship_permission'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.nonce_factory']
			);
		};

		$container['multilingualpress.term_translation_simple_ui'] = function ( Container $container ) {

			return new Term\MetaBox\UI\SimpleTermTranslator(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.server_request']
			);
		};
	}

	/**
	 * Registers the translator services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_translators( Container $container ) {

		$container['multilingualpress.front_page_translator'] = function ( Container $container ) {

			return new Translator\FrontPageTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.post_translator'] = function ( Container $container ) {

			return new Translator\PostTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.post_type_translator'] = function ( Container $container ) {

			return new Translator\PostTypeTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.search_translator'] = function ( Container $container ) {

			return new Translator\SearchTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.term_translator'] = function ( Container $container ) {

			return new Translator\TermTranslator(
				$container['multilingualpress.type_factory'],
				$container['multilingualpress.wpdb']
			);
		};
	}

	/**
	 * Bootstraps all post translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_post_translation( Container $container ) {

		$meta_box_registrar = $container['multilingualpress.post_meta_box_registrar'];

		$ui_registry = $container['multilingualpress.meta_box_ui_registry'];

		$ui_registry->register_ui(
			$container['multilingualpress.post_translation_advanced_ui'],
			$meta_box_registrar
		);

		$ui_registry->register_ui(
			$container['multilingualpress.post_translation_simple_ui'],
			$meta_box_registrar
		);

		add_action( 'admin_init', function () use ( $meta_box_registrar ) {

			$meta_box_registrar->register_meta_boxes();
		}, 0 );

		$post_translation_ui = $container['multilingualpress.post_translation_simple_ui'];

		// For the moment, let's set select here the UI for posts
		add_filter( MetaBoxUIRegistry::FILTER_SELECT_UI, function ( $ui, $registrar ) use (
			$meta_box_registrar,
			$post_translation_ui
		) {

			return $registrar === $meta_box_registrar ? $post_translation_ui : $ui;
		}, 10, 2 );

		add_action( Post\PostMetaBoxRegistrar::ACTION_INIT_META_BOXES, function () use (
			$meta_box_registrar,
			$ui_registry
		) {

			$meta_box_registrar->set_ui( $ui_registry->selected_ui( $meta_box_registrar ) );
		}, 0 );

		add_action( Post\PostMetaBoxRegistrar::ACTION_SAVE_META_BOXES, function () use ( $container ) {

			$container['multilingualpress.http_post_request_globals_manipulator']->clear_data();
		} );

		add_action( Post\PostMetaBoxRegistrar::ACTION_SAVED_META_BOXES, function () use ( $container ) {

			$container['multilingualpress.http_post_request_globals_manipulator']->restore_data();
		} );
	}

	/**
	 * Bootstraps all term translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_term_translation( Container $container ) {

		$meta_box_registrar = $container['multilingualpress.term_meta_box_registrar'];

		$ui_registry = $container['multilingualpress.meta_box_ui_registry'];

		$ui_registry->register_ui(
			$container['multilingualpress.term_translation_simple_ui'],
			$meta_box_registrar
		);

		add_action( 'admin_init', function () use ( $ui_registry, $meta_box_registrar ) {

			$meta_box_registrar->register_meta_boxes();
		}, 0 );

		$term_translation_ui = $container['multilingualpress.term_translation_simple_ui'];

		// For the moment, let's set select here the UI for terms
		add_filter( MetaBoxUIRegistry::FILTER_SELECT_UI, function ( $ui, $registrar ) use (
			$meta_box_registrar,
			$term_translation_ui
		) {

			return $registrar === $meta_box_registrar ? $term_translation_ui : $ui;
		}, 10, 2 );

		add_action( Term\TermMetaBoxRegistrar::ACTION_INIT_META_BOXES, function () use (
			$ui_registry,
			$meta_box_registrar
		) {

			$meta_box_registrar->with_ui( $ui_registry->selected_ui( $meta_box_registrar ) );
		}, 0 );

		add_action( Term\TermMetaBoxRegistrar::ACTION_SAVE_META_BOXES, function () use ( $container ) {

			$container['multilingualpress.http_post_request_globals_manipulator']->clear_data();
		} );

		add_action( Term\TermMetaBoxRegistrar::ACTION_SAVED_META_BOXES, function () use ( $container ) {

			$container['multilingualpress.http_post_request_globals_manipulator']->restore_data();
		} );
	}

	/**
	 * Bootstraps the translator services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_translators( Container $container ) {

		$translations = $container['multilingualpress.translations'];

		$translations->register_translator(
			$container['multilingualpress.front_page_translator'],
			WordPressRequestContext::TYPE_FRONT_PAGE
		);

		$translations->register_translator(
			$container['multilingualpress.post_translator'],
			WordPressRequestContext::TYPE_SINGULAR
		);

		$translations->register_translator(
			$container['multilingualpress.post_type_translator'],
			WordPressRequestContext::TYPE_POST_TYPE_ARCHIVE
		);

		$translations->register_translator(
			$container['multilingualpress.search_translator'],
			WordPressRequestContext::TYPE_SEARCH
		);

		$translations->register_translator(
			$container['multilingualpress.term_translator'],
			WordPressRequestContext::TYPE_TERM_ARCHIVE
		);
	}
}
