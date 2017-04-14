<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
class ActivePostTypes {

	const FILTER_ACTIVE_POST_TYPES = 'multilingualpress.active_post_types';

	const DEFAULT_ACTIVE_POST_TYPES = [ 'post', 'page' ];

	/**
	 * @var array
	 */
	private $allowed_post_types_slugs;

	/**
	 * Returns the allowed post type slugs.
	 *
	 * @return string[] Allowed post type slugs.
	 */
	public function names(): array {

		if ( null !== $this->allowed_post_types_slugs ) {
			return $this->allowed_post_types_slugs;
		}

		/**
		 * Filters the allowed post types.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $post_types Allowed post type slugs.
		 */
		$post_types = (array) apply_filters( self::FILTER_ACTIVE_POST_TYPES, self::DEFAULT_ACTIVE_POST_TYPES );

		$this->allowed_post_types_slugs = array_filter( $post_types, 'post_type_exists' );

		return $this->allowed_post_types_slugs;
	}

	/**
	 * Returns the allowed post type object.
	 *
	 * @return \WP_Post_Type[] Allowed post type objects.
	 */
	public function objects(): array {

		return array_map( 'get_post_type_object', $this->names() );
	}

	/**
	 * Returns the allowed post type object.
	 *
	 * @param \string[] $post_type
	 *
	 * @return bool
	 */
	public function includes( string ...$post_type ): bool {

		return (bool) array_intersect( $post_type, $this->names() );
	}
}
