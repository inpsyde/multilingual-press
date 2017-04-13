<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;

/**
 * Interface for all post meta updater implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post
 * @since   3.0.0
 */
interface PostMetaUpdater extends MetadataUpdater {

	/**
	 * Returns an instance with the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object to set.
	 *
	 * @return PostMetaUpdater
	 */
	public function with_post( \WP_Post $post ): PostMetaUpdater;

	/**
	 * Returns an instance with the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param SourcePostSaveContext $save_context Save context object to set.
	 *
	 * @return PostMetaUpdater
	 */
	public function with_save_context( SourcePostSaveContext $save_context ): PostMetaUpdater;
}
