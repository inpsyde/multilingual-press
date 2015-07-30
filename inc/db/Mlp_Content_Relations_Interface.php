<?php # -*- coding: utf-8 -*-
/**
 * Relationships between content blocks (posts, terms, whatever).
 *
 * @version 2015.07.30
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
interface Mlp_Content_Relations_Interface {

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

}
