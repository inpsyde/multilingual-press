<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation;

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

		$this->register_translator_package( $container );

		$this->register_post_metabox_package( $container );
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

		$this->bootstrap_translator_package( $container );

		$this->bootstrap_post_metabox_package( $container );
	}

	/**
	 * Register translator submodule.
	 *
	 * @param Container $container
	 */
	private function register_translator_package( Container $container ) {

		$container['multilingualpress.front_page_translator'] = function ( Container $container ) {

			return new Translator\FrontPageTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.post_request_data_manipulator'] = function () {

			return new Request\FullRequestDataManipulator( Request\RequestDataManipulator::METHOD_POST );
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
	 * Register post metabox submodule.
	 *
	 * @param Container $container
	 */
	private function register_post_metabox_package( Container $container ) {

		$container['multilingualpress.post_metabox_factory'] = function ( Container $container ) {

			return new Post\MetaboxFactory(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.site_settings_repository']
			);
		};

		$container['multilingualpress.post_permission_checker'] = function ( Container $container ) {

			return new Post\PermissionChecker( $container['multilingualpress.content_relations'] );
		};

		$container['multilingualpress.post_metabox_registrar'] = function ( Container $container ) {

			return new Post\PostMetaboxRegistrar(
				$container['multilingualpress.post_metabox_factory'],
				$container['multilingualpress.post_permission_checker'],
				$container['multilingualpress.request'],
				$container['multilingualpress.nonce_factory']
			);
		};

		$container->share( 'multilingualpress.post_translation_ui_registry', function () {

			return ( new Post\TranslationUIRegistry() )
				->register_ui( new Post\TranslationAdvancedUI() )
				->register_ui( new Post\TranslationSimpleUI() );
		} );
	}

	/**
	 * Bootstraps translator submodule.
	 *
	 * @param Container $container
	 */
	private function bootstrap_translator_package( Container $container ) {

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

		if ( $container['request']->server_value( 'REQUEST_METHOD' ) === 'POST' ) {

			$post_request_data_manipulator = $container['multilingualpress.post_request_data_manipulator'];

			add_action( 'mlp_before_post_synchronization', [ $post_request_data_manipulator, 'clear_data' ] );
			add_action( 'mlp_after_post_synchronization', [ $post_request_data_manipulator, 'restore_data' ] );

			add_action( 'mlp_before_term_synchronization', [ $post_request_data_manipulator, 'clear_data' ] );
			add_action( 'mlp_after_term_synchronization', [ $post_request_data_manipulator, 'restore_data' ] );
		}
	}

	/**
	 * Bootstraps post metabox submodule.
	 *
	 * @param Container $container
	 */
	private function bootstrap_post_metabox_package( Container $container ) {

		add_action( 'admin_init', function () use ( $container ) {

			/** @var Post\TranslationUIRegistry $ui_registry */
			$ui_registry = $container['multilingualpress.post_translation_ui_registry'];
			$ui_registry->setup();

			/** @var Post\PostMetaboxRegistrar $box_registrar */
			$box_registrar = $container['multilingualpress.post_metabox_registrar'];
			$box_registrar->register_metaboxes();
		}, 0 );
	}
}
