<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;
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
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations $content_relations
	 * @param ServerRequest    $server_request
	 */
	public function __construct( ContentRelations $content_relations, ServerRequest $server_request ) {

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;
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
	 * Initializes the user interface.
	 *
	 * This will be called early to allow wiring up of early-running hooks, for example, 'wp_ajax_{$action}'.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function initialize() {

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

		add_filter( TranslationMetadataUpdater::FILTER_SAVE_POST, function (
			\WP_Post $remote_post,
			int $remote_site_id,
			ServerRequest $server_request,
			SourcePostSaveContext $save_context
		) {

			$updater = new SimplePostTranslatorUpdater(
				$this->content_relations,
				$server_request,
				$save_context
			);

			return $updater->update( $remote_post, $remote_site_id );
		}, 30, 5 );
	}

	/**
	 * Registers the view of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_view() {

		$fields = new SimplePostTranslatorFields();

		$this->inject_into_view( function (
			/** @noinspection PhpUnusedParameterInspection */
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null,
			array $data = []
		) use ( $fields ) {

			$fields->render_top_fields( $post, $remote_site_id, $remote_post );
		}, TranslationMetaBoxView::POSITION_TOP );

		$this->inject_into_view( function (
			/** @noinspection PhpUnusedParameterInspection */
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null,
			array $data = []
		) use ( $fields ) {

			$fields->render_main_fields( $post, $remote_site_id, $remote_post );
		}, TranslationMetaBoxView::POSITION_MAIN );

		$this->inject_into_view( function (
			/** @noinspection PhpUnusedParameterInspection */
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null,
			array $data = []
		) use ( $fields ) {

			$fields->render_bottom_fields( $post, $remote_site_id, $remote_post );
		} );
	}
}
