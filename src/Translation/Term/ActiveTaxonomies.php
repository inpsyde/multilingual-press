<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term;

/**
 * Simple read-only storage for taxonomies active for MultilingualPress.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
class ActiveTaxonomies {

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_ACTIVE_TAXONOMIES = 'multilingualpress.active_taxonomies';

	/**
	 * @var array
	 */
	private $active_taxonomy_names;

	/**
	 * Returns the allowed taxonomy names.
	 *
	 * @return string[] Allowed taxonomy names.
	 */
	public function names(): array {

		if ( null !== $this->active_taxonomy_names ) {
			return $this->active_taxonomy_names;
		}

		/**
		 * Filters the allowed taxonomies.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $active_taxonomies Allowed taxonomy names.
		 */
		$active_taxonomies = (array) apply_filters( self::FILTER_ACTIVE_TAXONOMIES, [] );

		$this->active_taxonomy_names = array_filter( array_unique( $active_taxonomies ), 'taxonomy_exists' );

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
	 * @param string[] ...$taxonomies Taxonomy names to check.
	 *
	 * @return bool
	 */
	public function includes( string ...$taxonomies ): bool {

		return ! array_diff( array_unique( $taxonomies ), $this->names() );
	}
}
