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

		return esc_html_x( 'Simple', 'Post Metabox UI title', 'multilingualpress');
	}

	/**
	 * @return void
	 */
	public function setup_view() {

		add_action( 'multilingualpress.post_translation_view', function ( TranslationMetaboxView $view ) {

		} );

		add_action(
			'multilingualpress.translation_meta_box_top',
			function( \WP_Post $post, int $remote_site_id, string $remote_language, \WP_Post $remote_post = null ) {

			},
			10,
			4
		);

		add_action(
			'multilingualpress.translation_meta_box_main',
			function( \WP_Post $post, int $remote_site_id, string $remote_language, \WP_Post $remote_post = null ) {

			},
			10,
			4
		);

		add_action(
			'multilingualpress.translation_meta_box_bottom',
			function( \WP_Post $post, int $remote_site_id, string $remote_language, \WP_Post $remote_post = null ) {

			},
			10,
			4
		);
	}

	/**
	 * @return void
	 */
	public function setup_updater() {

		add_action( 'multilingualpress.post_translation_updater', function ( TranslationMetaboxUpdater $updater ) {

		} );
	}
}