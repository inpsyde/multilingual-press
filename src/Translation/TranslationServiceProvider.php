<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation;

use Inpsyde\MultilingualPress\Common\WordPressRequestContext;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI\AdvancedPostTranslator;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI\SimplePostTranslator;
use Inpsyde\MultilingualPress\Translation\Post\MetaBoxFactory;
use Inpsyde\MultilingualPress\Translation\Post\PostMetaBoxRegistrar;
use Inpsyde\MultilingualPress\Translation\Post\TranslationUIRegistry;
use Inpsyde\MultilingualPress\Translation\Translator\FrontPageTranslator;
use Inpsyde\MultilingualPress\Translation\Translator\PostTranslator;
use Inpsyde\MultilingualPress\Translation\Translator\PostTypeTranslator;
use Inpsyde\MultilingualPress\Translation\Translator\SearchTranslator;
use Inpsyde\MultilingualPress\Translation\Translator\TermTranslator;

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

		$container['multilingualpress.post_request_data_manipulator'] = function () {

			return new FullRequestDataManipulator( RequestDataManipulator::METHOD_POST );
		};

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

		$container['multilingualpress.post_translation_meta_box_factory'] = function ( Container $container ) {

			return new MetaBoxFactory(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.content_relations']
			);
		};

		$container['multilingualpress.post_translation_meta_box_registrar'] = function ( Container $container ) {

			return new PostMetaBoxRegistrar(
				$container['multilingualpress.post_translation_meta_box_factory'],
				$container['multilingualpress.relationship_permission'],
				$container['multilingualpress.server_request'],
				$container['multilingualpress.nonce_factory']
			);
		};

		$container->share( 'multilingualpress.post_translation_ui_registry', function () {

			return new TranslationUIRegistry();
		} );
	}

	/**
	 * Registers the term translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_term_translation( Container $container ) {

		// TODO
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

			return new FrontPageTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.post_translator'] = function ( Container $container ) {

			return new PostTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.post_type_translator'] = function ( Container $container ) {

			return new PostTypeTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.search_translator'] = function ( Container $container ) {

			return new SearchTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.term_translator'] = function ( Container $container ) {

			return new TermTranslator(
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

		$ui_registry = $container['multilingualpress.post_translation_ui_registry'];

		$ui_registry->register_ui( new AdvancedPostTranslator() );

		$ui_registry->register_ui( new SimplePostTranslator() );

		$box_registrar = $container['multilingualpress.post_translation_meta_box_registrar'];

		add_action( 'admin_init', function () use ( $ui_registry, $box_registrar ) {

			$ui_registry->setup();

			$box_registrar->register_meta_boxes();
		}, 0 );

		if ( 'POST' === $container['multilingualpress.server_request']->server_value( 'REQUEST_METHOD' ) ) {
			$post_request_data_manipulator = $container['multilingualpress.post_request_data_manipulator'];

			add_action( 'mlp_before_post_synchronization', [ $post_request_data_manipulator, 'clear_data' ] );
			add_action( 'mlp_after_post_synchronization', [ $post_request_data_manipulator, 'restore_data' ] );
		}
	}

	/**
	 * Bootstraps all term translation services.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function bootstrap_term_translation( Container $container ) {

		if ( 'POST' === $container['multilingualpress.server_request']->server_value( 'REQUEST_METHOD' ) ) {
			$post_request_data_manipulator = $container['multilingualpress.post_request_data_manipulator'];

			add_action( 'mlp_before_term_synchronization', [ $post_request_data_manipulator, 'clear_data' ] );
			add_action( 'mlp_after_term_synchronization', [ $post_request_data_manipulator, 'restore_data' ] );
		}
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
