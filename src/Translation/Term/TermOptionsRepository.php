<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Translation\Term;

/**
 * Interface for all term options repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
interface TermOptionsRepository {

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
	public function fetch_related_site_terms( int $base_site_id, string $taxonomy );

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
	public function get_terms_for_site( int $site_id, string $taxonomy ): array;
}
