<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\RelationshipControlView;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxView;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\ViewInjection;
use Inpsyde\MultilingualPress\Translation\Post\RelationshipContext;

/**
 * Advanced post translation user interface implementation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
final class AdvancedPostTranslator implements MetaBoxUI {

	use ViewInjection;

	/**
	 * User interface ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ID = 'multilingualpress.advanced_post_translator';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var RelationshipControlView
	 */
	private $relationship_control_view;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations        $content_relations
	 * @param ServerRequest           $server_request
	 * @param RelationshipControlView $relationship_control_view
	 * @param AssetManager            $asset_manager
	 */
	public function __construct(
		ContentRelations $content_relations,
		ServerRequest $server_request,
		RelationshipControlView $relationship_control_view,
		AssetManager $asset_manager
	) {

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;

		$this->relationship_control_view = $relationship_control_view;

		$this->asset_manager = $asset_manager;
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

		static $done;
		if ( $done ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_' . AdvancedPostTranslatorAJAXHandler::AJAX_ACTION, function () {

				( new AdvancedPostTranslatorAJAXHandler( $this->server_request ) )->handle_request();
			} );

			$done = true;
		}
	}

	/**
	 * Returns the name of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string Name of the user interface.
	 */
	public function name(): string {

		return _x( 'Advanced', 'Post translation UI name', 'multilingualpress' );
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

			$updater = new AdvancedPostTranslatorUpdater(
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

		$this->asset_manager->enqueue_style( 'multilingualpress-admin' );

		$fields = new AdvancedPostTranslatorFields( $this->asset_manager );

		/** @noinspection PhpUnusedParameterInspection */
		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null,
			array $data = []
		) use ( $fields ) {

			// If remote post is trashed show a notice and do nothing.
			if ( $this->is_remote_post_trashed( $remote_post ) ) {
				$this->show_trashed_message();

				return;
			}

			echo $fields->top_fields( $post, $remote_site_id, $remote_post );
		}, TranslationMetaBoxView::POSITION_TOP );

		/** @noinspection PhpUnusedParameterInspection */
		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null,
			array $data = []
		) use ( $fields ) {

			if ( ! $this->is_remote_post_trashed( $remote_post ) ) {
				echo $fields->main_fields( $post, $remote_site_id, $remote_post );
			}
		}, TranslationMetaBoxView::POSITION_MAIN );

		/** @noinspection PhpUnusedParameterInspection */
		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null,
			array $data = []
		) use ( $fields ) {

			if ( ! $this->is_remote_post_trashed( $remote_post ) ) {
				$this->relationship_control_view->render( new RelationshipContext( [
					RelationshipContext::KEY_REMOTE_POST_ID => $remote_post->ID ?? 0,
					RelationshipContext::KEY_REMOTE_SITE_ID => $remote_site_id,
					RelationshipContext::KEY_SOURCE_POST_ID => $post->ID,
					RelationshipContext::KEY_SOURCE_SITE_ID => get_current_blog_id(),
				] ) );

				echo $fields->bottom_fields( $post, $remote_site_id, $remote_post );
			}
		} );
	}

	/**
	 * @param \WP_Post|null $remote_post
	 *
	 * @return bool
	 */
	private function is_remote_post_trashed( \WP_Post $remote_post = null ): bool {

		return $remote_post && $remote_post->post_status === 'trash';
	}

	/**
	 * Shows a warning message in the meta box that the remote post is trashed.
	 *
	 * @return void
	 */
	private function show_trashed_message() {

		?>
		<div class="mlp-warning">
			<p>
				<?php
				_e(
					'The remote post is trashed. You are not able to edit it here. If you want to, restore the remote post. Also mind the options below.',
					'multilingualpress'
				);
				?>
			</p>
		</div>
		<?php
	}
}
