<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use function Inpsyde\MultilingualPress\site_exists;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
class PostTranslationGuard {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var array
	 */
	private $linked_posts = [];

	/**
	 * Constructor.
	 *
	 * @param ContentRelations $content_relations
	 */
	public function __construct( ContentRelations $content_relations ) {

		$this->content_relations = $content_relations;
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	public function is_source_post_translatable( \WP_Post $post ): bool {

		$post_type = get_post_type_object( $post->post_type );

		return
			$post_type instanceof \WP_Post_Type
			&& current_user_can( $post_type->cap->edit_post, $post->ID );
	}

	/**
	 * @param \WP_Post $source_post
	 * @param int|null $remote_site_id
	 *
	 * @return bool
	 */
	public function is_remote_post_translatable( \WP_Post $source_post, int $remote_site_id = null ): bool {

		if ( null === $remote_site_id || $remote_site_id === (int) get_current_blog_id() ) {
			return $this->is_source_post_translatable( $source_post );
		}

		$post_type = get_post_type_object( $source_post->post_type );
		if ( ! $post_type instanceof \WP_Post_Type ) {
			return false;
		}

		if ( ! site_exists( $remote_site_id ) ) {
			return false;
		}

		$remote_post_id = $this->remote_post_id( $source_post, $remote_site_id );
		if ( ! $remote_post_id ) {
			return false;
		}

		return current_user_can_for_blog( $remote_site_id, $post_type->cap->edit_post, $remote_post_id );
	}

	/**
	 * @param  \WP_Post $source_post
	 * @param  int      $site_id
	 *
	 * @return int
	 */
	private function remote_post_id( \WP_Post $source_post, int $site_id ): int {

		$linked = $this->linked_to( $source_post );

		if ( empty( $linked[ $site_id ] ) ) {
			return 0;
		}

		$post = get_blog_post( $site_id, $linked[ $site_id ] );

		return $post ? (int) $post->ID : 0;
	}

	/**
	 * @param \WP_Post $source_post
	 *
	 * @return array
	 */
	private function linked_to( \WP_Post $source_post ): array {

		$post_id = (int) $source_post->ID;

		if ( array_key_exists( $post_id, $this->linked_posts ) ) {
			return $this->linked_posts[ $post_id ];
		}

		$this->linked_posts[ $post_id ] = $this->content_relations->get_relations(
			get_current_blog_id(),
			$source_post->ID,
			'post'
		);

		return $this->linked_posts[ $post_id ];
	}
}