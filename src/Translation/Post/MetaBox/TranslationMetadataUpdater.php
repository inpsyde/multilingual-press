<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaUpdater;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\AllowedPostTypes;

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
	const ACTION_SAVE_POST = 'multilingualpress.post_translation_meta_box_save_post';

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var AllowedPostTypes
	 */
	private $post_types;

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
	 * @param int              $site_id        Site ID.
	 * @param SiteRelations    $site_relations Site relations object.
	 * @param AllowedPostTypes $post_types     Allowed post type object.
	 * @param \WP_Post         $remote_post    Optional. Remote post object. Defaults to null.
	 */
	public function __construct(
		int $site_id,
		SiteRelations $site_relations,
		AllowedPostTypes $post_types,
		\WP_Post $remote_post = null
	) {

		$this->site_id = $site_id;

		$this->site_relations = $site_relations;

		$this->post_types = $post_types;

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
	 * @param \WP_Post $post Post object to set.
	 *
	 * @return PostMetaUpdater
	 */
	public function with_post( \WP_Post $post ): PostMetaUpdater {

		$this->post = $post;

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
	public function with_save_context( SourcePostSaveContext $save_context ): PostMetaUpdater {

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

		if ( ! $this->post instanceof \WP_Post || ! $this->save_context instanceof SourcePostSaveContext ) {
			return false;
		}

		if ( ! $this->remote_post ) {
			$this->remote_post = new \WP_Post( (object) [
				'post_type'   => $this->post->post_type,
				'post_status' => 'draft',
			] );
		}

		/**
		 * Action to allow updaters from UIs to save the remote post
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
			self::ACTION_SAVE_POST,
			$this->remote_post,
			$this->site_id,
			$this->post,
			$server_request,
			$this->save_context
		);

		return true;

	}
}
