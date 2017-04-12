<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
final class AllowedPostTypes implements \ArrayAccess {

	const FILTER_ALLOWED_POST_TYPES = 'multilingualpress.allowed_post_types';

	const DEFAULT_ALLOWED_POST_TYPES = [ 'post', 'page' ];

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
		$post_types = (array) apply_filters( self::FILTER_ALLOWED_POST_TYPES, self::DEFAULT_ALLOWED_POST_TYPES );

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
	 * Returns true if the given post type exists and it is allowed.
	 *
	 * @param string $offset Post type slug
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {

		return in_array( $offset, $this->names(), true );
	}

	/**
	 * Returns a post type object for given post type name, or null if not allowed.
	 *
	 * @param string $offset Post type slug
	 *
	 * @return null|\WP_Post_Type
	 */
	public function offsetGet( $offset ) {

		return $this->offsetExists( $offset ) ? get_post_type_object( $offset ) : null;
	}

	/**
	 * Disabled.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {

		throw new \BadMethodCallException( __CLASS__ . " is read only." );
	}

	/**
	 * Disabled.
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {

		throw new \BadMethodCallException( __CLASS__ . " is read only." );
	}
}
