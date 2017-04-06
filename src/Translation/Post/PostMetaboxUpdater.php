<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface PostMetaboxUpdater extends MetaboxUpdater {

	/**
	 * @param \WP_Post $post
	 *
	 * @return PostMetaboxUpdater
	 */
	public function with_post( \WP_Post $post ): PostMetaboxUpdater;

}