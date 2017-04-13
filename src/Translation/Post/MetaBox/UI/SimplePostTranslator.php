<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxView;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\ViewInjection;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
final class SimplePostTranslator implements MetaBoxUI {

	use ViewInjection;

	/**
	 * User interface ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID = 'multilingualpress.simple_post_translator';

	/**
	 * Initialize the UI. This will be called early to allow setup of early hooks like 'wp_ajax_*'.
	 *
	 * @return void
	 */
	public function initialize() {
		// TODO: Implement initialize() method.
	}

	/**
	 * Returns the ID of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string ID of the user interface.
	 */
	public function id(): string {

		return self::ID;
	}

	/**
	 * Returns the name of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string Name of the user interface.
	 */
	public function name(): string {

		return _x( 'Simple', 'Post translation UI name', 'multilingualpress' );
	}

	/**
	 * Registers the updater of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_updater() {

		add_action( TranslationMetaBoxController::ACTION_INITIALIZED_UPDATER, function (
			TranslationMetadataUpdater $updater
		) {

			// TODO: Make use of $updater->with_data() here?
		} );
	}

	/**
	 * Registers the view of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_view() {

		add_action( TranslationMetaBoxController::ACTION_INITIALIZED_VIEW, function ( TranslationMetaBoxView $view ) {

			// TODO: Make use of $view->with_data() here?
		} );

		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null
		) {

			// TODO: Render fields?
		}, TranslationMetaBoxView::POSITION_TOP );

		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null
		) {

			// TODO: Render fields?
		}, TranslationMetaBoxView::POSITION_MAIN );

		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null
		) {

			// TODO: Render fields?
		} );
	}
}
