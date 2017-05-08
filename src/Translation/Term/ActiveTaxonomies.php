<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term;

use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

/**
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
class ActiveTaxonomies {

	const FILTER_ACTIVE_TAXONOMIES = 'multilingualpress.active_taxonomies';

	/**
	 * @var array
	 */
	private $active_taxonomy_names;

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ActivePostTypes $active_post_types
	 */
	public function __construct( ActivePostTypes $active_post_types ) {

		$this->active_post_types = $active_post_types;
	}

	/**
	 * Returns the allowed taxonomy names.
	 *
	 * @return string[] Allowed taxonomy names.
	 */
	public function names(): array {

		if ( null !== $this->active_taxonomy_names ) {
			return $this->active_taxonomy_names;
		}

		$all_taxonomies = [];
		foreach ( $this->active_post_types->names() as $post_type ) {
			$post_type_taxonomies = get_object_taxonomies( (object) compact( 'post_type' ) );
			$all_taxonomies       = array_merge( $all_taxonomies, $post_type_taxonomies );
		}

		/**
		 * Filters the allowed taxonomies.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $active_taxonomies Allowed taxonomy names.
		 */
		$active_taxonomies = (array) apply_filters( self::FILTER_ACTIVE_TAXONOMIES, array_unique( $all_taxonomies ) );

		$this->active_taxonomy_names = array_filter( $active_taxonomies, function( $taxonomy ) {
			return is_string( $taxonomy ) && taxonomy_exists( $taxonomy );
		} );

		return $this->active_taxonomy_names;
	}

	/**
	 * Returns the allowed taxonomy objects.
	 *
	 * @return \WP_Taxonomy[] Allowed taxonomy objects.
	 */
	public function objects(): array {

		return array_map( 'get_taxonomy', $this->names() );
	}

	/**
	 * Returns true if given taxonomy names are allowed.
	 *
	 * @param string[] $taxonomies
	 *
	 * @return bool
	 */
	public function includes( string ...$taxonomies ): bool {

		return ! array_diff( array_unique( $taxonomies ), $this->names() );
	}
}
