<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * Simple read-only storage for post types activ for MultilingualPress.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
class ActivePostTypes {

	/**
	 * Post type slugs active by default.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	const DEFAULT_ACTIVE_POST_TYPES = [
		'page',
		'post',
	];

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_ACTIVE_POST_TYPES = 'multilingualpress.active_post_types';

	/**
	 * @var string[]
	 */
	private $active_post_types_slugs;

	/**
	 * Returns the active post type slugs.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Active post type slugs.
	 */
	public function names(): array {

		if ( null !== $this->active_post_types_slugs ) {
			return $this->active_post_types_slugs;
		}

		/**
		 * Filters the active post type slugs.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $active_post_types Active post type slugs.
		 */
		$active_post_types = (array) apply_filters( self::FILTER_ACTIVE_POST_TYPES, self::DEFAULT_ACTIVE_POST_TYPES );

		$this->active_post_types_slugs = array_filter( array_unique( $active_post_types ), 'post_type_exists' );

		return $this->active_post_types_slugs;
	}

	/**
	 * Returns the active post type objects.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Post_Type[] Active post type objects.
	 */
	public function objects(): array {

		return array_map( 'get_post_type_object', $this->names() );
	}

	/**
	 * Checks if all given post type slugs are active.
	 *
	 * @since 3.0.0
	 *
	 * @param \string[] $post_types Post type slugs to check.
	 *
	 * @return bool Whether or not all given post type slugs are active.
	 */
	public function includes( string ...$post_types ): bool {

		return ! array_diff( array_unique( $post_types ), $this->names() );
	}
}
