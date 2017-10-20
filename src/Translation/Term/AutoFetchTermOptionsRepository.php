<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation\Term;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\NetworkState;

/**
 * Term options repository implementations that automatically fetches related site terms on first usage.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
final class AutoFetchTermOptionsRepository implements TermOptionsRepository {

	/**
	 * @var string[][][]
	 */
	private $options;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations $site_relations Site relations API object.
	 */
	public function __construct( SiteRelations $site_relations ) {

		$this->site_relations = $site_relations;
	}

	/**
	 * Queries all terms of all sites related to the site with the given ID.
	 *
	 * This speeds up looking through a number of sites one by one.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $base_site_id Site ID.
	 * @param string $taxonomy     Taxonomy name.
	 *
	 * @return void
	 */
	public function fetch_related_site_terms( int $base_site_id, string $taxonomy ) {

		$related_site_ids = $this->site_relations->get_related_site_ids( $base_site_id, true );
		if ( ! $related_site_ids ) {
			return;
		}

		$network_state = NetworkState::create();

		foreach ( $related_site_ids as $related_site_id ) {
			switch_to_blog( $related_site_id );

			$taxonomy_object = get_taxonomy( $taxonomy );
			if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
				continue;
			}

			$terms = get_terms( $taxonomy, [
				'hide_empty' => false,
			] );

			$options = array_reduce( $terms, function ( array $options, \WP_Term $term ) use ( $taxonomy ) {

				$option = $term->name;

				if ( is_taxonomy_hierarchical( $taxonomy ) ) {
					foreach ( (array) get_ancestors( $term->term_id, $taxonomy ) as $ancestor ) {
						$ancestor_term = get_term( $ancestor, $taxonomy );
						if ( $ancestor_term ) {
							$option = "{$ancestor_term->name}/{$option}";
						}
					}
				}

				$options[ (int) $term->term_taxonomy_id ] = $option;

				return $options;
			}, [] );

			uasort( $options, 'strcasecmp' );

			$this->options[ $related_site_id ][ $taxonomy ] = $options;
		}

		$network_state->restore();
	}

	/**
	 * Returns the term options for the given taxonomy in the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $site_id  Site ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return string[] Array with term taxonomy IDs as keys and term name paths as values.
	 */
	public function get_terms_for_site( int $site_id, string $taxonomy ): array {

		$this->fetch_related_site_terms( $site_id, $taxonomy );

		return $this->options[ $site_id ][ $taxonomy ] ?? [];
	}
}
