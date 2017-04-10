<?php # -*- coding: utf-8 -*-

// TODO

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxController;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post
 * @since   3.0.0
 */
class MetaBoxFactory {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * Constructor.
	 *
	 * @param SiteRelations    $site_relations
	 * @param ContentRelations $content_relations
	 */
	public function __construct( SiteRelations $site_relations, ContentRelations $content_relations ) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return SiteAwareMetaBoxController[]
	 */
	public function create_meta_boxes( \WP_Post $post ): array {

		$allowed_post_types = $this->allowed_post_types();
		if ( ! $allowed_post_types || ! in_array( $post->post_type, $allowed_post_types, true ) ) {
			return [];
		}

		$current_site_id = (int) get_current_blog_id();

		$related_site_ids = $this->site_relations->get_related_site_ids( $current_site_id, false );
		if ( ! $related_site_ids ) {
			return [];
		}

		$related_post_ids = $this->content_relations->get_relations( $current_site_id, $post->ID, 'post' );

		return array_map( function ( int $site_id ) use ( $related_post_ids, $allowed_post_types ) {

			$related_post = empty( $related_post_ids[ $site_id ] )
				? null
				: get_blog_post( $site_id, $related_post_ids[ $site_id ] );

			return $this->create_meta_box_for_site( $site_id, $allowed_post_types, $related_post );
		}, $related_site_ids );
	}

	/**
	 * @param int           $site_id
	 * @param string[]      $post_types
	 * @param \WP_Post|null $related_post
	 *
	 * @return SiteAwareMetaBoxController
	 */
	private function create_meta_box_for_site(
		int $site_id,
		array $post_types,
		\WP_Post $related_post = null
	): SiteAwareMetaBoxController {

		return new TranslationMetaBoxController( $site_id, $post_types, $related_post );
	}

	/**
	 * @return string[]
	 */
	private function allowed_post_types() {

		/**
		 * Filter the allowed post types.
		 *
		 * @param string[] $allowed_post_types Allowed post type names.
		 */
		$allowed_post_types = (array) apply_filters( 'multilingualpress.allowed_post_types', [ 'post', 'page' ] );

		return array_filter( $allowed_post_types, 'is_string' );
	}
}
