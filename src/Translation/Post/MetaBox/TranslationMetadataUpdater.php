<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaUpdater;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

/**
 * Metadata updater implementation for post translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class TranslationMetadataUpdater implements PostMetaUpdater {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_SAVE_POST = 'multilingualpress.post_translation_meta_box_save';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_SAVED_POST = 'multilingualpress.post_translation_meta_box_saved';

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @var int
	 */
	private $remote_site_id;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * @var \WP_Post
	 */
	private $remote_post;

	/**
	 * @var SourcePostSaveContext
	 */
	private $save_context;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int             $site_id           Site ID.
	 * @param SiteRelations   $site_relations    Site relations object.
	 * @param ActivePostTypes $active_post_types Active post types object.
	 * @param \WP_Post        $remote_post       Optional. Remote post object. Defaults to null.
	 */
	public function __construct(
		int $site_id,
		SiteRelations $site_relations,
		ActivePostTypes $active_post_types,
		\WP_Post $remote_post = null
	) {

		$this->remote_site_id = $site_id;

		$this->site_relations = $site_relations;

		$this->active_post_types = $active_post_types;

		$this->remote_post = $remote_post;
	}

	/**
	 * Returns an instance with the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to be set.
	 *
	 * @return MetadataUpdater
	 */
	public function with_data( array $data ): MetadataUpdater {

		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * Returns an instance with the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param SourcePostSaveContext $save_context Save context object to set.
	 *
	 * @return PostMetaUpdater
	 */
	public function with_post_save_context( SourcePostSaveContext $save_context ): PostMetaUpdater {

		$this->save_context = $save_context;

		return $this;
	}

	/**
	 * Updates the metadata included in the given server request.
	 *
	 * The update happen in the context of remote post site.
	 *
	 * @since 3.0.0
	 *
	 * @param ServerRequest $server_request Server request object.
	 *
	 * @return bool True when update is successful.
	 */
	public function update( ServerRequest $server_request ): bool {

		if ( ! $this->save_context ) {
			return false;
		}

		if ( ! $this->remote_post ) {
			$this->remote_post = new \WP_Post( (object) [
				'post_type'   => $this->save_context[ SourcePostSaveContext::POST_TYPE ],
				'post_status' => 'draft',
			] );
		}

		/**
		 * Filter remote post instance.
		 *
		 * Updaters from UI should hook here and return the maybe updated remote post.
		 *
		 * Returning anything but a post object or a post object with invalid ID means something failed in the update.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post              $remote_post    Remote post object being saved.
		 * @param int                   $remote_site_id Remote site ID.
		 * @param ServerRequest         $server_request Server request object.
		 * @param SourcePostSaveContext $save_context   Save context object.
		 */
		$remote_post = apply_filters(
			self::FILTER_SAVE_POST,
			$this->remote_post,
			$this->remote_site_id,
			$server_request,
			$this->save_context
		);

		if ( ! $remote_post instanceof \WP_Post || ! $remote_post->ID ) {
			return false;
		}

		/**
		 * Action fired after remote post has been saved by updaters provided by UI.
		 *
		 * This provides access on just saved remote post alongside source save context, to allow custom saving
		 * routines, (e.g. for custom post meta) no matter the UI in use.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post              $remote_post    Remote post object being saved.
		 * @param int                   $remote_site_id Remote site ID.
		 * @param string                $source_post    Source post object.
		 * @param ServerRequest         $server_request Server request object.
		 * @param SourcePostSaveContext $save_context   Save context object.
		 */
		do_action(
			self::ACTION_SAVED_POST,
			$this->remote_post,
			$this->remote_site_id,
			$this->save_context,
			$server_request
		);

		return true;
	}
}
