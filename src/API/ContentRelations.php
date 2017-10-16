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
	 * Content type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const CONTENT_TYPE_POST = 'post';

	/**
	 * Content type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const CONTENT_TYPE_TERM = 'term';

	/**
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_POST_TYPE = 'multilingualpress.content_relations_post_type';

	/**
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_POST_STATUS = 'multilingualpress.content_relations_post_status';

	/**
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_TAXONOMY = 'multilingualpress.content_relations_taxonomy';

	/**
	 * Deletes all relations for posts that don't exist (anymore).
	 *
	 * @since 3.0.0
	 *
	 * @param string $type Content type.
	 *
	 * @return bool Whether or not all relations were deleted successfully.
	 */
	public function delete_all_relations_for_invalid_content( string $type ): bool;

	/**
	 * Deletes all relations for sites that don't exist (anymore).
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not all relations were deleted successfully.
	 */
	public function delete_all_relations_for_invalid_sites(): bool;

	/**
	 * Deletes all relations for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not all relations were deleted successfully.
	 */
	public function delete_all_relations_for_site( int $site_id ): bool;

	/**
	 * Deletes a relation according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]  $content_ids Array with site IDs as keys and content IDs as values.
	 * @param string $type        Content type.
	 *
	 * @return bool Whether or not the relation was deleted successfully.
	 */
	public function delete_relation( array $content_ids, string $type ): bool;

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
	 * Returns the content IDs for the given relationship ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $relationship_id Relationship ID.
	 *
	 * @return int[] Array with site IDs as keys and content IDs as values.
	 */
	public function get_content_ids( int $relationship_id ): array;

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
	public function get_relations( int $site_id, int $content_id, string $type ): array;

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
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Another site ID.
	 *
	 * @return bool Whether or not all posts were related successfully.
	 */
	public function relate_all_posts( int $site_1, int $site_2 ): bool;

	/**
	 * Relates all terms between the given source site and the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * term taxonomy IDs. Furthermore, the current site is assumed to be either the source site or the destination site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Another site ID.
	 *
	 * @return bool Whether or not all terms were related successfully.
	 */
	public function relate_all_terms( int $site_1, int $site_2 ): bool;

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
