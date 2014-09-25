<?php # -*- coding: utf-8 -*-
/**
 * Relationships between content blocks (posts, terms, whatever).
 *
 * @version 2014.08.17
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Content_Relations_Interface {

	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id // post_id or term_taxonomy_id
	 * @param  int    $target_content_id // the same
	 * @param  string $type
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
	 * Returns an array with site ID as keys and content ID as values.
	 *
	 * @param  int    $source_site_id
	 * @param  int    $source_content_id
	 * @param  string $type
	 * @return array
	 */
	public function get_relations(
		$source_site_id,
		$source_content_id,
		$type = 'post'
	);

	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id post_id or term_taxonomy_id
	 * @param  int    $target_content_id if 0, all target content ids are removed
	 * @param  string $type
	 * @return bool
	 */
	public function delete_relation(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id = 0,
		$type = 'post'
	);

	/**
	 * @param  int    $source_site_id
	 * @param  int    $target_site_id
	 * @param  int    $source_content_id  // post_id or term_taxonomy_id
	 * @param  int    $target_content_id  // the same
	 * @param  string $type
	 * @return array
	 */
	public function get_translation_ids(
		$source_site_id,
		$target_site_id,
		$source_content_id,
		$target_content_id,
		$type
	);
}