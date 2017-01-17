<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation;

use Inpsyde\MultilingualPress\Common\Request;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
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

		$container['multilingualpress.front_page_translator'] = function ( Container $container ) {

			return new FrontPageTranslator(
				$container['multilingualpress.type_factory']
			);
		};

		$container['multilingualpress.post_request_data_manipulator'] = function () {

			return new FullRequestDataManipulator( RequestDataManipulator::METHOD_POST );
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
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		$translations = $container['multilingualpress.translations'];

		$translations->register_translator(
			$container['multilingualpress.front_page_translator'],
			Request::TYPE_FRONT_PAGE
		);

		$translations->register_translator(
			$container['multilingualpress.post_translator'],
			Request::TYPE_SINGULAR
		);

		$translations->register_translator(
			$container['multilingualpress.post_type_translator'],
			Request::TYPE_POST_TYPE_ARCHIVE
		);

		$translations->register_translator(
			$container['multilingualpress.search_translator'],
			Request::TYPE_SEARCH
		);

		$translations->register_translator(
			$container['multilingualpress.term_translator'],
			Request::TYPE_TERM_ARCHIVE
		);

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			$post_request_data_manipulator = $container['multilingualpress.post_request_data_manipulator'];

			add_action( 'mlp_before_post_synchronization', [ $post_request_data_manipulator, 'clear_data' ] );
			add_action( 'mlp_after_post_synchronization', [ $post_request_data_manipulator, 'restore_data' ] );

			add_action( 'mlp_before_term_synchronization', [ $post_request_data_manipulator, 'clear_data' ] );
			add_action( 'mlp_after_term_synchronization', [ $post_request_data_manipulator, 'restore_data' ] );
		}
	}
}
