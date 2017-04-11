<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxController;

/**
 * Factory for post translation meta box controller objects.
 *
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
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations    $site_relations    Site relations API object.
	 * @param ContentRelations $content_relations Content relations API object.
	 */
	public function __construct( SiteRelations $site_relations, ContentRelations $content_relations ) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;
	}

	/**
	 * Returns an array with all post translation meta box controllers for the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return SiteAwareMetaBoxController[] Post translation meta box controllers.
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
	 * Returns a post translation meta box controller according to the given site and post data.
	 *
	 * @param int      $site_id      Site ID.
	 * @param string[] $post_types   One or more post type slugs.
	 * @param \WP_Post $related_post Optional. Related post object. Defaults to null.
	 *
	 * @return SiteAwareMetaBoxController Post translation meta box controller.
	 */
	private function create_meta_box_for_site(
		int $site_id,
		array $post_types,
		\WP_Post $related_post = null
	): SiteAwareMetaBoxController {

		return new TranslationMetaBoxController( $site_id, $post_types, $related_post );
	}

	/**
	 * Returns the allowed post type slugs.
	 *
	 * @todo Make this a method on a dedicated data object (maybe extending \ArrayObject)...
	 *
	 * @return string[] One or more post type slugs.
	 */
	private function allowed_post_types() {

		/**
		 * Filters the allowed post types.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $allowed_post_types Allowed post type slugs.
		 */
		$allowed_post_types = (array) apply_filters( 'multilingualpress.allowed_post_types', [ 'post', 'page' ] );

		return array_filter( $allowed_post_types, 'is_string' );
	}
}
