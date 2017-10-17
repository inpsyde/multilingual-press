<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;
use Inpsyde\MultilingualPress\Translation\Term\ActiveTaxonomies;

/**
 * Content relations API implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class WPDBContentRelations implements ContentRelations {

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * @var ActiveTaxonomies
	 */
	private $active_taxonomies;

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $relationships_table;

	/**
	 * @var string
	 */
	private $table;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb            $db                  WordPress database object.
	 * @param Table            $table               Content relations table object.
	 * @param Table            $relationships_table Relations table object.
	 * @param ActivePostTypes  $active_post_types   Active post type storage object.
	 * @param ActiveTaxonomies $active_taxonomies   Active taxonomy storage object.
	 */
	public function __construct(
		\wpdb $db,
		Table $table,
		Table $relationships_table,
		ActivePostTypes $active_post_types,
		ActiveTaxonomies $active_taxonomies
	) {

		$this->db = $db;

		$this->table = $table->name();

		$this->relationships_table = $relationships_table->name();

		$this->active_post_types = $active_post_types;

		$this->active_taxonomies = $active_taxonomies;
	}

	/**
	 * Creates a new relationship for the given type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $type Content type.
	 *
	 * @return int Relationship ID.
	 */
	public function create_relationship_for_type( string $type ): int {

		if ( $this->db->insert( $this->relationships_table, [
			Table\RelationshipsTable::COLUMN_TYPE => $type,
		], '%s' ) ) {
			return (int) $this->db->insert_id;
		}

		return 0;
	}

	/**
	 * Deletes all relations for posts that don't exist (anymore).
	 *
	 * @since 3.0.0
	 *
	 * @param string $type Content type.
	 *
	 * @return bool Whether or not all relations were deleted successfully.
	 */
	public function delete_all_relations_for_invalid_content( string $type ): bool {

		$relationship_ids = $this->get_relationship_ids_for_type( $type );
		if ( ! $relationship_ids ) {
			return true;
		}

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query_template = sprintf(
			'
SELECT %2$s
FROM %1$s
WHERE %3$s = %%d
	AND %2$s NOT IN (%%s)
	AND %4$s IN (%%s)',
			$this->table,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID
		);

		$relationship_ids = join( ',', $relationship_ids );

		$network_state = NetworkState::create();

		$site_ids = get_sites( [
			'fields' => 'ids',
		] );

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );

			$query = $this->db->prepare( $query_template, [
				$site_id,
				join( ',', $this->get_existing_content_ids( $type ) ),
				$relationship_ids,
			] );

			$content_ids = $this->db->get_col( $query );
			foreach ( $content_ids as $content_id ) {
				$this->delete_relation( [
					$site_id => (int) $content_id,
				], $type );
			}
		}

		$network_state->restore();

		return true;
	}

	/**
	 * Deletes all relations for sites that don't exist (anymore).
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not all relations were deleted successfully.
	 */
	public function delete_all_relations_for_invalid_sites(): bool {

		$query = sprintf(
			'
SELECT DISTINCT %2$s
FROM %1$s
WHERE %2$s NOT IN (
		SELECT blog_id
		FROM %3$s
	)',
			$this->table,
			Table\ContentRelationsTable::COLUMN_SITE_ID,
			$this->db->blogs
		);

		$site_ids = $this->db->get_col( $query );
		foreach ( $site_ids as $site_id ) {
			if ( ! $this->delete_all_relations_for_site( (int) $site_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Deletes all relations for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not all relations were deleted successfully.
	 */
	public function delete_all_relations_for_site( int $site_id ): bool {

		$relationship_ids = $this->get_relationship_ids_for_site( $site_id );

		return array_reduce( $relationship_ids, function ( bool $success, int $relationship_id ) use ( $site_id ) {

			return $success && $this->delete_relation_for_site( $relationship_id, $site_id );
		}, true );
	}

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
	public function delete_relation( array $content_ids, string $type ): bool {

		$relationship_id = $this->get_relationship_id( $content_ids, $type );
		if ( ! $relationship_id ) {
			return true;
		}

		$site_ids = array_map( 'intval', array_keys( $content_ids ) );

		return array_reduce( $site_ids, function ( bool $success, int $site_id ) use ( $relationship_id ) {

			return $success && $this->delete_relation_for_site( $relationship_id, $site_id );
		}, true );
	}

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
	public function duplicate_relations( int $source_site_id, int $destination_site_id, string $type = '' ): int {

		if ( $type ) {
			return $this->duplicate_relations_of_type( $source_site_id, $destination_site_id, $type );
		}

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
INSERT INTO %1$s
SELECT %2$s, %%d, %3$s
FROM %1$s
WHERE %4$s = %%d',
			$this->table,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID
		);
		$query = $this->db->prepare( $query, $destination_site_id, $source_site_id );

		return (int) $this->db->query( $query );
	}

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
	public function get_content_id( int $relationship_id, int $site_id ): int {

		$content_ids = $this->get_content_ids( $relationship_id );

		return $content_ids[ $site_id ] ?? 0;
	}

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
	): int {

		$relations = $this->get_relations( $site_id, $content_id, $type );

		return $relations[ $target_site_id ] ?? 0;
	}

	/**
	 * Returns the content IDs for the given relationship ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $relationship_id Relationship ID.
	 *
	 * @return int[] Array with site IDs as keys and content IDs as values.
	 */
	public function get_content_ids( int $relationship_id ): array {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT %2$s, %3$s
FROM %1$s
WHERE %4$s = %%d',
			$this->table,
			Table\ContentRelationsTable::COLUMN_SITE_ID,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID
		);
		$query = $this->db->prepare( $query, $relationship_id );

		$rows = $this->db->get_results( $query, ARRAY_A );

		return array_reduce( $rows, function ( array $content_ids, array $row ) {

			$content_id = (int) $row[ Table\ContentRelationsTable::COLUMN_CONTENT_ID ];

			$content_ids[ (int) $row[ Table\ContentRelationsTable::COLUMN_SITE_ID ] ] = $content_id;

			return $content_ids;
		}, [] );
	}

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
	public function get_relations( int $site_id, int $content_id, string $type ): array {

		$relationship_id = $this->get_relationship_id_single( $site_id, $content_id, $type );

		return $relationship_id ? $this->get_content_ids( $relationship_id ) : [];
	}

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
	public function get_relationship_id( array $content_ids, string $type, bool $create = false ): int {

		if ( ! $content_ids ) {
			// Error: No contents given!
			return 0;
		}

		$relationship_id = $this->get_relationship_id_multiple( $content_ids, $type );

		if ( ! $relationship_id && $create ) {
			return $this->create_relationship_for_type( $type );
		}

		return $relationship_id;
	}

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
	public function has_site_relations( int $site_id, string $type = '' ): bool {

		if ( $type ) {
			return $this->has_site_relations_of_type( $site_id, $type );
		}

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT %2$s
FROM %1$s
WHERE %3$s = %%d
LIMIT 1',
			$this->table,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID
		);
		$query = $this->db->prepare( $query, $site_id );

		return (bool) $this->db->query( $query );
	}

	/**
	 * Relates all posts between the two sites with the given IDs.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * post IDs. Furthermore, the current site ID is assumed to be one of the given two.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Another site ID.
	 *
	 * @return bool Whether or not all posts were related successfully.
	 */
	public function relate_all_posts( int $site_1, int $site_2 ): bool {

		$post_ids = $this->get_post_ids_to_relate();

		return array_reduce( $post_ids, function ( bool $success, int $post_id ) use ( $site_1, $site_2 ) {

			$relationship_id = $this->get_relationship_id( [
				$site_1 => $post_id,
				$site_2 => $post_id,
			], ContentRelations::CONTENT_TYPE_POST, true );

			return
				$success
				&& $this->set_relation( $relationship_id, $site_1, $post_id )
				&& $this->set_relation( $relationship_id, $site_2, $post_id );
		}, true );
	}

	/**
	 * Relates all terms between the two sites with the given IDs.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * term taxonomy IDs. Furthermore, the current site ID is assumed to be one of the given two.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_1 Site ID.
	 * @param int $site_2 Another site ID.
	 *
	 * @return bool Whether or not all terms were related successfully.
	 */
	public function relate_all_terms( int $site_1, int $site_2 ): bool {

		$term_taxonomy_ids = $this->get_term_taxonomy_ids_to_relate();

		return array_reduce( $term_taxonomy_ids, function ( bool $success, int $ttid ) use ( $site_1, $site_2 ) {

			$relationship_id = $this->get_relationship_id( [
				$site_1 => $ttid,
				$site_2 => $ttid,
			], ContentRelations::CONTENT_TYPE_TERM, true );

			return
				$success
				&& $this->set_relation( $relationship_id, $site_1, $ttid )
				&& $this->set_relation( $relationship_id, $site_2, $ttid );
		}, true );
	}

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
	public function set_relation( int $relationship_id, int $site_id, int $content_id ): bool {

		if ( 0 === $content_id ) {
			return false !== $this->delete_relation_for_site( $relationship_id, $site_id );
		}

		$current_content_id = $this->get_content_id( $relationship_id, $site_id );
		if ( $current_content_id ) {
			if ( $current_content_id === $content_id ) {
				return true;
			}

			// Delete different relation of the given site.
			$this->delete_relation_for_site( $relationship_id, $site_id, false );
		}

		$type = $this->get_relationship_type( $relationship_id );
		if ( $type ) {
			$current_relationship_id = $this->get_relationship_id_single( $site_id, $content_id, $type );
			if ( $current_relationship_id && $current_relationship_id !== $relationship_id ) {
				// Delete different relation of the given content element.
				$this->delete_relation_for_site( $current_relationship_id, $site_id );
			}
		}

		return $this->insert_relation( $relationship_id, $site_id, $content_id );
	}

	/**
	 * Deletes the relation for the given arguments.
	 *
	 * @param int  $relationship_id Relationship ID.
	 * @param int  $site_id         Site ID.
	 * @param bool $delete          Optional. Delete relationship if less than three content elements? Defaults to true.
	 *
	 * @return bool Whether or the relation was deleted successfully.
	 */
	private function delete_relation_for_site( int $relationship_id, int $site_id, bool $delete = true ): bool {

		$content_ids = $this->get_content_ids( $relationship_id );

		if (
			count( $content_ids ) < 3
			&& $delete
			&& ! empty( $content_ids[ $site_id ] )
		) {
			return $this->delete_relationship( $relationship_id );
		}

		return false !== $this->db->delete( $this->table, [
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID => $relationship_id,
			Table\ContentRelationsTable::COLUMN_SITE_ID         => $site_id,
		], '%d' );
	}

	/**
	 * Removes the relationship as well as all relations with the given relationship ID.
	 *
	 * @param int $relationship_id Relationship ID.
	 *
	 * @return bool Whether or not the relationship was deleted successfully.
	 */
	private function delete_relationship( int $relationship_id ): bool {

		if ( false === $this->db->delete( $this->relationships_table, [
			Table\RelationshipsTable::COLUMN_ID => $relationship_id,
		], '%d' ) ) {
			return false;
		}

		return false !== $this->db->delete( $this->table, [
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID => $relationship_id,
		], '%d' );
	}

	/**
	 * Copies all relations of the given content type from the given source site to the given destination site.
	 *
	 * This method is suited to be used after site duplication, because both sites are assumed to have the exact same
	 * content IDs.
	 *
	 * @param int    $source_site_id      Source site ID.
	 * @param int    $destination_site_id Destination site ID.
	 * @param string $type                Content type.
	 *
	 * @return int The number of relations duplicated.
	 */
	private function duplicate_relations_of_type( int $source_site_id, int $destination_site_id, string $type ): int {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
INSERT INTO %1$s
SELECT t.%2$s, %%d, t.%3$s
FROM %1$s t
JOIN %4$s r ON t.%2$s = r.%5$s
WHERE t.%6$s = %%d
	AND r.%7$s = %%s',
			$this->table,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			$this->relationships_table,
			Table\RelationshipsTable::COLUMN_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID,
			Table\RelationshipsTable::COLUMN_TYPE
		);
		$query = $this->db->prepare( $query, $destination_site_id, $source_site_id, $type );

		return (int) $this->db->query( $query );
	}

	/**
	 * Returns the IDs of all existing content elements of the given type in the current site.
	 *
	 * @param string $type Content type.
	 *
	 * @return int[] Content IDs.
	 */
	private function get_existing_content_ids( string $type ): array {

		switch ( $type ) {
			case self::CONTENT_TYPE_POST:
				$content_ids = $this->db->get_col( sprintf(
					'SELECT ID FROM %s',
					$this->db->posts
				) );
				break;

			case self::CONTENT_TYPE_TERM:
				$content_ids = $this->db->get_col( sprintf(
					'SELECT term_taxonomy_id FROM %s',
					$this->db->term_taxonomy
				) );
				break;

			default:
				return [];
		}

		return array_map( 'intval', $content_ids );
	}

	/**
	 * Returns the IDs of the posts to relate for the current site.
	 *
	 * @return int[] Post IDs.
	 */
	private function get_post_ids_to_relate(): array {

		$query = sprintf(
			'
SELECT ID
FROM %1$s
WHERE 1 = 1',
			$this->db->posts
		);

		$post_status = [
			'draft',
			'future',
			'pending',
			'private',
			'publish',
		];
		/**
		 * Filters the post status to be used for post relations.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $post_status Post status array.
		 */
		$post_status = array_filter( (array) apply_filters( ContentRelations::FILTER_POST_STATUS, $post_status ) );
		if ( $post_status ) {
			$query .= sprintf(
				'
	AND post_status IN (%1$s)',
				"'" . implode( "', '", $post_status ) . "'"
			);
		}

		$post_type = $this->active_post_types->names();
		/**
		 * Filters the post type to be used for post relations.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $post_type Post type array.
		 */
		$post_type = array_filter( (array) apply_filters( ContentRelations::FILTER_POST_TYPE, $post_type ) );
		if ( $post_type ) {
			$query .= sprintf(
				'
	AND post_type IN (%1$s)',
				"'" . implode( "', '", $post_type ) . "'"
			);
		}

		return array_map( 'intval', $this->db->get_col( $query ) );
	}

	/**
	 * Returns the relationship ID for the given arguments.
	 *
	 * @param int[]  $content_ids Array with site IDs as keys and content IDs as value.
	 * @param string $type        Content type.
	 *
	 * @return int Relationship ID.
	 */
	private function get_relationship_id_multiple( array $content_ids, string $type ): int {

		$relationship_id = 0;

		foreach ( $content_ids as $site_id => $content_id ) {
			$new_relationship_id = $this->get_relationship_id_single( (int) $site_id, (int) $content_id, $type );
			if ( ! $new_relationship_id ) {
				continue;
			}

			if ( ! $relationship_id ) {
				$relationship_id = $new_relationship_id;
			} elseif ( $relationship_id !== $new_relationship_id ) {
				// Error: Different relationship IDs!
				return 0;
			}
		}

		return $relationship_id;
	}

	/**
	 * Returns the relationship ID for the given arguments.
	 *
	 * @param int    $site_id    Site ID.
	 * @param int    $content_id Content ID.
	 * @param string $type       Content type.
	 *
	 * @return int Relationship ID.
	 */
	private function get_relationship_id_single( int $site_id, int $content_id, string $type ): int {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT r.%2$s
FROM %1$s r
INNER JOIN %3$s t ON r.%2$s = t.%4$s
WHERE t.%5$s = %%d
	AND t.%6$s = %%d
	AND r.%7$s = %%s',
			$this->relationships_table,
			Table\RelationshipsTable::COLUMN_ID,
			$this->table,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			Table\RelationshipsTable::COLUMN_TYPE
		);
		$query = $this->db->prepare( $query, $site_id, $content_id, $type );

		return (int) $this->db->get_var( $query );
	}

	/**
	 * Returns the relationship IDs for the site with the given ID.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return int[] Relationship IDs.
	 */
	private function get_relationship_ids_for_site( int $site_id ): array {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT %2$s
FROM %1$s
WHERE %3$s = %%d',
			$this->table,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID
		);
		$query = $this->db->prepare( $query, $site_id );

		return array_map( 'intval', $this->db->get_col( $query ) );
	}

	/**
	 * Returns the relationship IDs for the given type.
	 *
	 * @param string $type Content type.
	 *
	 * @return int[] Relationship IDs.
	 */
	private function get_relationship_ids_for_type( string $type ): array {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT %2$s
FROM %1$s
WHERE %3$s = %%s',
			$this->relationships_table,
			Table\RelationshipsTable::COLUMN_ID,
			Table\RelationshipsTable::COLUMN_TYPE
		);
		$query = $this->db->prepare( $query, $type );

		return array_map( 'intval', $this->db->get_col( $query ) );
	}

	/**
	 * Return the content type for the relationship with the given ID.
	 *
	 * @param int $relationship_id Relationship ID.
	 *
	 * @return string Content type.
	 */
	private function get_relationship_type( int $relationship_id ): string {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT %2$s
FROM %1$s
WHERE %3$s = %%d',
			$this->relationships_table,
			Table\RelationshipsTable::COLUMN_TYPE,
			Table\RelationshipsTable::COLUMN_ID
		);
		$query = $this->db->prepare( $query, $relationship_id );

		return (string) $this->db->get_var( $query );
	}

	/**
	 * Returns the IDs of the terms to relate for the current site.
	 *
	 * @return int[] Post IDs.
	 */
	private function get_term_taxonomy_ids_to_relate(): array {

		$query = sprintf(
			'
SELECT term_taxonomy_id
FROM %1$s
WHERE 1 = 1',
			$this->db->term_taxonomy
		);

		$taxonomy = $this->active_taxonomies->names();
		/**
		 * Filters the taxonomy to be used for term relations.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $taxonomy Taxonomy array.
		 */
		$taxonomy = array_filter( (array) apply_filters( ContentRelations::FILTER_TAXONOMY, $taxonomy ) );
		if ( $taxonomy ) {
			$query .= sprintf(
				'
	AND taxonomy IN (%1$s)',
				"'" . implode( "', '", $taxonomy ) . "'"
			);
		}

		return array_map( 'intval', $this->db->get_col( $query ) );
	}

	/**
	 * Checks if the site with the given ID has any relations of the given content type.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $type    Content type.
	 *
	 * @return bool Whether or not the site with the given ID has any relations of the given content type.
	 */
	private function has_site_relations_of_type( int $site_id, string $type ): bool {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'
SELECT t.%2$s
FROM %1$s t
JOIN %3$s r ON t.%4$s = r.%5$s
WHERE t.%6$s = %%d
	AND r.%7$s = %%s
LIMIT 1',
			$this->table,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID,
			$this->relationships_table,
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID,
			Table\RelationshipsTable::COLUMN_ID,
			Table\ContentRelationsTable::COLUMN_SITE_ID,
			Table\RelationshipsTable::COLUMN_TYPE
		);
		$query = $this->db->prepare( $query, $site_id, $type );

		return (bool) $this->db->query( $query );
	}

	/**
	 * Inserts a new relation with the given values.
	 *
	 * @param int $relationship_id Relationship ID.
	 * @param int $site_id         Site ID.
	 * @param int $content_id      Content ID.
	 *
	 * @return bool Whether or not the relation was inserted successfully.
	 */
	private function insert_relation( int $relationship_id, int $site_id, int $content_id ): bool {

		return (bool) $this->db->insert( $this->table, [
			Table\ContentRelationsTable::COLUMN_RELATIONSHIP_ID => $relationship_id,
			Table\ContentRelationsTable::COLUMN_SITE_ID         => $site_id,
			Table\ContentRelationsTable::COLUMN_CONTENT_ID      => $content_id,
		], '%d' );
	}
}
