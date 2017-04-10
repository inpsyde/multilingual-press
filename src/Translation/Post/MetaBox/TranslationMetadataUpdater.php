<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaUpdater;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;

/**
 * Metadata updater implementation for post translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class TranslationMetadataUpdater implements PostMetaUpdater {

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var \WP_Post
	 */
	private $remote_post;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int      $site_id     Site ID.
	 * @param \WP_Post $remote_post Optional. Remote post object. Defaults to null.
	 */
	public function __construct( int $site_id, \WP_Post $remote_post = null ) {

		$this->site_id = $site_id;

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

		$clone = clone $this;

		$clone->data = array_merge( $this->data, $data );

		return $clone;
	}

	/**
	 * Updates the metadata included in the given server request.
	 *
	 * @since 3.0.0
	 *
	 * @param ServerRequest $request Server request object.
	 *
	 * @return bool Whether or not the metadata was updated successfully.
	 */
	public function update( ServerRequest $request ): bool {

		// TODO: Implement update() method.

		return true;
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

		$clone = clone $this;

		$clone->post = $post;

		return $clone;
	}
}
