<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term;

use Inpsyde\MultilingualPress\API\ContentRelations;

use function Inpsyde\MultilingualPress\site_exists;

/**
 * Permission checker to be used to either permit or prevent access to terms.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
class RelationshipPermission {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var int[][]
	 */
	private $related_terms = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ContentRelations $content_relations Content relations API object.
	 */
	public function __construct( ContentRelations $content_relations ) {

		$this->content_relations = $content_relations;
	}

	/**
	 * Checks if the current user can edit (or create) a term in the site with the given ID that is related to given
	 * term in the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term $term            Term object in the current site.
	 * @param int      $related_site_id Related site ID.
	 *
	 * @return bool Whether or not the related term of the given term in the given site is editable.
	 */
	public function is_related_term_editable( \WP_Term $term, int $related_site_id ): bool {

		if ( ! site_exists( $related_site_id ) ) {
			return false;
		}

		$taxonomy = get_taxonomy( $term->taxonomy );
		if ( ! $taxonomy instanceof \WP_Taxonomy ) {
			return false;
		}

		if ( ! $term->term_id ) {
			return current_user_can_for_blog( $related_site_id, $taxonomy->cap->edit_terms );
		}

		$related_term_id = $this->get_related_term_id( $term, $related_site_id );
		if ( 0 > $related_term_id ) {
			return false;
		}

		if ( 0 < $related_term_id ) {
			return current_user_can_for_blog( $related_site_id, 'edit_term', $related_term_id );
		}

		return current_user_can_for_blog( $related_site_id, $taxonomy->cap->edit_terms );
	}

	/**
	 * Returns the ID of the term in the site with the given ID that is related to given term in the current site.
	 *
	 * @param \WP_Term $term            Term object in the current site.
	 * @param int      $related_site_id Related site ID.
	 *
	 * @return int Term ID, or 0.
	 */
	private function get_related_term_id( \WP_Term $term, int $related_site_id ): int {

		$related_terms = $this->get_related_terms( (int) $term->term_id );
		if ( empty( $related_terms[ $related_site_id ] ) ) {
			return 0;
		}

		$switched = false;
		if ( (int) get_current_blog_id() !== $related_site_id ) {
			$switched = true;
			switch_to_blog( $related_site_id );
		}

		// If the taxonomy is not registered in the remote site we return -1 so we can return false in calling method.
		if ( $switched && ! taxonomy_exists( $term->taxonomy ) ) {
			restore_current_blog();

			return -1;
		}

		// This is just to be extra careful in case the term has been deleted via MySQL etc.
		$related_term = get_term( $term->term_id, $term->taxonomy );

		if ( $switched ) {
			restore_current_blog();
		}

		return $related_term instanceof \WP_Term ? (int) $related_term->term_id : 0;
	}

	/**
	 * Returns an array with the IDs of all related terms for the term with the given ID.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @return int[] The array with site IDs as keys and term IDs as values.
	 */
	private function get_related_terms( int $term_id ): array {

		if ( array_key_exists( $term_id, $this->related_terms ) ) {
			return $this->related_terms[ $term_id ];
		}

		$this->related_terms[ $term_id ] = $this->content_relations->get_relations(
			get_current_blog_id(),
			$term_id,
			'term'
		);

		return $this->related_terms[ $term_id ];
	}
}
