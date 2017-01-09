<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

// TODO: For now, this is just a copy of the old API. Functionally refactor this as soon as the structural one is done.

/**
 * Interface for all content relations API implementations.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
interface ContentRelations {

	/**
	 * Delete a relation according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return int Number of deleted rows
	 */
	public function delete_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id = 0,
		$type = 'post'
	);

	/**
	 * Deletes all relations for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return int Number of deleted rows.
	 */
	public function delete_relations_for_site( $site_id );

	/**
	 * Return the term taxonomy ID of the given target site for the given source term.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return int
	 */
	public function get_element_for_site(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$type
	);

	/**
	 * Return the existing translation IDs according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	public function get_existing_translation_ids(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	);

	/**
	 * Return an array with site IDs as keys and content IDs as values.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	public function get_relations( $source_site_id, $source_content_id, $type = 'post' );

	/**
	 * Return the existing (or new) translation IDs according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return array
	 */
	public function get_translation_ids(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	);

	/**
	 * Set a relation according to the given parameters.
	 *
	 * @param int    $source_site_id    Source blog ID.
	 * @param int    $target_site_id    Target blog ID.
	 * @param int    $source_content_id Source post ID or term taxonomy ID.
	 * @param int    $target_content_id Target post ID or term taxonomy ID.
	 * @param string $type              Content type.
	 *
	 * @return bool
	 */
	public function set_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type = 'post'
	);

	/**
	 * Checks if the site with the given ID has any relations of the given (or any) content type.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $site_id Site ID.
	 * @param string $type    Optional. Content type. Defaults to empty string.
	 *
	 * @return bool Whether or not the site with the given ID has any relations of the given (or any) content type.
	 */
	public function has_site_relations( $site_id, $type = '' );

	/**
	 * Copies all relations of the given (or any) content type from the given source site to the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * content IDs.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $source_site_id      Source site ID.
	 * @param int    $destination_site_id Destination site ID.
	 * @param string $type                Optional. Content type. Defaults to empty string.
	 *
	 * @return int The number of relations duplicated.
	 */
	public function duplicate_relations( $source_site_id, $destination_site_id, $type = '' );

	/**
	 * Relates all posts between the given source site and the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * post IDs. Furthermore, the current site is assumed to be either the source site or the destination site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $source_site_id      Source site ID.
	 * @param int $destination_site_id Destination site ID.
	 *
	 * @return int The number of relations inserted.
	 */
	public function relate_all_posts( $source_site_id, $destination_site_id );

	/**
	 * Relates all terms between the given source site and the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * term taxonomy IDs. Furthermore, the current site is assumed to be either the source site or the destination site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $source_site_id      Source site ID.
	 * @param int $destination_site_id Destination site ID.
	 *
	 * @return int The number of relations inserted.
	 */
	public function relate_all_terms( $source_site_id, $destination_site_id );
}
