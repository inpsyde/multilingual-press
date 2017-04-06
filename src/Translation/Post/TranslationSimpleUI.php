<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class TranslationSimpleUI implements TranslationMetaboxUI {

	const ID = 'mlp_simple';

	/**
	 * @return string
	 */
	public function id(): string {

		return self::ID;
	}

	/**
	 * @return string
	 */
	public function title(): string {

		return esc_html_x( 'Simple', 'Post Metabox UI title', 'multilingualpress' );
	}

	/**
	 * @return void
	 */
	public function setup_view() {

		add_action( TranslationMetabox::ACTION_INIT_VIEW, function ( TranslationMetaboxView $view ) {
			// @TODO Do we need to set data into view here?
		} );

		TranslationViewInjector::inject_in_view(
			TranslationMetaboxView::POSITION_TOP,
			function ( \WP_Post $post, int $remote_site_id, string $remote_language, \WP_Post $remote_post = null ) {
				// @TODO setup fields
			}
		);

		TranslationViewInjector::inject_in_view(
			TranslationMetaboxView::POSITION_MAIN,
			function ( \WP_Post $post, int $remote_site_id, string $remote_language, \WP_Post $remote_post = null ) {
				// @TODO setup fields
			}
		);

		TranslationViewInjector::inject_in_view(
			TranslationMetaboxView::POSITION_BOTTOM,
			function ( \WP_Post $post, int $remote_site_id, string $remote_language, \WP_Post $remote_post = null ) {
				// @TODO setup fields
			}
		);
	}

	/**
	 * @return void
	 */
	public function setup_updater() {

		add_action( TranslationMetabox::ACTION_INIT_UPDATER, function ( TranslationMetaboxUpdater $updater ) {
			// @TODO Do we need to set data into updater here?
		} );
	}
}