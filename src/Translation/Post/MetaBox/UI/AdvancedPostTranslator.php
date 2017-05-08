<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxUI;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxView;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\ViewInjection;

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
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations $content_relations
	 * @param AssetManager     $asset_manager
	 * @param ServerRequest    $server_request
	 */
	public function __construct(
		ContentRelations $content_relations,
		AssetManager $asset_manager,
		ServerRequest $server_request
	) {

		$this->content_relations = $content_relations;

		$this->asset_manager = $asset_manager;

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

		static $done;
		if ( ! $done && wp_doing_ajax() ) {
			add_action( 'wp_ajax_' . AdvancedPostTranslatorAJAXHandler::AJAX_ACTION, function () {

				( new AdvancedPostTranslatorAJAXHandler( $this->server_request ) )();
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

		$fields = new AdvancedPostTranslatorFields();

		// Add inputs to the top of meta box
		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null
		) use ( $fields ) {

			// If remote post is trashed show a notice and do nothing.
			if ( $this->is_remote_post_trashed( $remote_post ) ) {
				$this->show_trashed_message();

				return;
			}

			$this->asset_manager->add_script_data( 'multilingualpress-admin', 'mlpCopyPostSettings', [
				'action' => AdvancedPostTranslatorAJAXHandler::AJAX_ACTION,
				'siteID' => get_current_blog_id(),
			] );

			echo $fields->top_fields( $post, $remote_site_id, $remote_post );

		}, TranslationMetaBoxView::POSITION_TOP );

		// Add inputs to the center of meta box
		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null
		) use ( $fields ) {

			if ( ! $this->is_remote_post_trashed( $remote_post ) ) {
				echo $fields->main_fields( $post, $remote_site_id, $remote_post );
			}

		}, TranslationMetaBoxView::POSITION_MAIN );

		// Add inputs to the bottom of meta box
		$this->inject_into_view( function (
			\WP_Post $post,
			int $remote_site_id,
			string $remote_language,
			\WP_Post $remote_post = null
		) use ( $fields ) {

			if ( ! $this->is_remote_post_trashed( $remote_post ) ) {
				echo $fields->bottom_fields( $post, $remote_site_id, $remote_post );
			}

		}, TranslationMetaBoxView::POSITION_BOTTOM );
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
