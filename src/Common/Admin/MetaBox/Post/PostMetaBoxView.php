<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxView;

/**
 * Interface for all post meta box view implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post
 * @since   3.0.0
 */
interface PostMetaBoxView extends MetaBoxView {

	/**
	 * Returns an instance with the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object to set.
	 *
	 * @return PostMetaBoxView
	 */
	public function with_post( \WP_Post $post ): PostMetaBoxView;
}
