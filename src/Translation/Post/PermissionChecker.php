<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;

use function Inpsyde\MultilingualPress\site_exists;

/**
 * Permission checker to be used to either permit or prevent access to posts.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
class PermissionChecker {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var int[][]
	 */
	private $related_posts = [];

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
	 * Checks if the current user can edit the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return bool Whether or not the given post is editable.
	 */
	public function is_post_editable( \WP_Post $post ): bool {

		$post_type = get_post_type_object( $post->post_type );
		if ( ! $post_type instanceof \WP_Post_Type ) {
			return false;
		}

		return current_user_can( $post_type->cap->edit_post, $post->ID );
	}

	/**
	 * Checks if the current user can edit (or create) the translation of the given post in the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $source_post    Source post object.
	 * @param int      $remote_site_id Remote site ID.
	 *
	 * @return bool Whether or not the translation of the given post in the given site is editable.
	 */
	public function is_translation_editable( \WP_Post $source_post, int $remote_site_id ): bool {

		$post_type = get_post_type_object( $source_post->post_type );
		if ( ! $post_type instanceof \WP_Post_Type ) {
			return false;
		}

		if ( ! site_exists( $remote_site_id ) ) {
			return false;
		}

		$remote_post_id = $this->get_remote_post_id( $source_post, $remote_site_id );
		if ( ! $remote_post_id ) {
			return current_user_can_for_blog( $remote_site_id, $post_type->cap->edit_others_posts );
		}

		return current_user_can_for_blog( $remote_site_id, $post_type->cap->edit_post, $remote_post_id );
	}

	/**
	 * Returns the post ID of the translation of the given post in the site with the given ID.
	 *
	 * @param \WP_Post $source_post Source post object.
	 * @param int      $site_id     Site ID.
	 *
	 * @return int Post ID, or 0.
	 */
	private function get_remote_post_id( \WP_Post $source_post, int $site_id ): int {

		$related_posts = $this->get_related_posts( (int) $source_post->ID );
		if ( empty( $related_posts[ $site_id ] ) ) {
			return 0;
		}

		// This is just to be extra careful in case the post has been deleted via MySQL etc.
		$post = get_blog_post( $site_id, $related_posts[ $site_id ] );

		return $post ? (int) $post->ID : 0;
	}

	/**
	 * Returns an array with the IDs of all related posts for the post with the given ID.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return int[] The array with site IDs as keys and post IDs as values.
	 */
	private function get_related_posts( int $post_id ): array {

		if ( array_key_exists( $post_id, $this->related_posts ) ) {
			return $this->related_posts[ $post_id ];
		}

		$this->related_posts[ $post_id ] = $this->content_relations->get_relations( get_current_blog_id(), $post_id );

		return $this->related_posts[ $post_id ];
	}
}
