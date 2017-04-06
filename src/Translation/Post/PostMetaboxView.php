<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxView;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface PostMetaboxView extends MetaboxView {

	/**
	 * @param \WP_Post $post
	 *
	 * @return PostMetaboxView
	 */
	public function with_post( \WP_Post $post ): PostMetaboxView;

}