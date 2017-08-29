<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

/**
 * Interface for all content relations API implementations.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
interface ContentRelations {

	/**
	 * Deletes a relation according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]  $content_ids Array with site IDs as keys and content IDs as values.
	 * @param string $type        Content type.
	 *
	 * @return int Number of deleted rows.
	 */
	public function delete_relation( array $content_ids, string $type ): int;

	/**
	 * Deletes all relations for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return int Number of deleted rows.
	 */
	public function delete_relations_for_site( int $site_id ): int;

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
	public function duplicate_relations( int $source_site_id, int $destination_site_id, string $type = '' ): int;

	/**
	 * Returns the content ID for the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int $relationship_id Relationship ID.
	 * @param int $site_id         Site ID.
	 *
	 * @return int Content ID.
	 */
	public function get_content_id( int $relationship_id, int $site_id ): int;

	/**
	 * Returns the content ID in the given target site for the given content element.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $site_id        Source site ID.
	 * @param int    $content_id     Source content ID.
	 * @param string $type           Content type.
	 * @param int    $target_site_id Target site ID.
	 *
	 * @return int Content ID.
	 */
	public function get_content_id_for_site(
		int $site_id,
		int $content_id,
		string $type,
		int $target_site_id
	): int;

	/**
	 * Returns all relations for the given content element.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $site_id    Site ID.
	 * @param int    $content_id Content ID.
	 * @param string $type       Content type.
	 *
	 * @return int[] Array with site IDs as keys and content IDs as values.
	 */
	public function get_relations( int $site_id, int $content_id, string $type = 'post' ): array;

	/**
	 * Returns the relationship ID for the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]  $content_ids Array with site IDs as keys and content IDs as values.
	 * @param string $type        Content type.
	 * @param bool   $create      Optional. Create a new relationship if not exists? Defaults to false.
	 *
	 * @return int Relationship ID.
	 */
	public function get_relationship_id( array $content_ids, string $type, bool $create = false ): int;

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
	public function has_site_relations( int $site_id, string $type = '' ): bool;

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
	public function relate_all_posts( int $source_site_id, int $destination_site_id ): int;

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
	public function relate_all_terms( int $source_site_id, int $destination_site_id ): int;

	/**
	 * Sets a relation according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int $relationship_id Relationship ID.
	 * @param int $site_id         Site ID.
	 * @param int $content_id      Content ID.
	 *
	 * @return bool Whether or not the relation was set successfully.
	 */
	public function set_relation( int $relationship_id, int $site_id, int $content_id ): bool;
}
