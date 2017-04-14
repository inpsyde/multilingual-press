<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\TranslationMetaBoxController;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;

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
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations    $site_relations    Site relations API object.
	 * @param ContentRelations $content_relations Content relations API object.
	 * @param ActivePostTypes  $active_post_types Allowed post type object.
	 */
	public function __construct(
		SiteRelations $site_relations,
		ContentRelations $content_relations,
		ActivePostTypes $active_post_types
	) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;

		$this->active_post_types = $active_post_types;
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

		if ( ! $this->active_post_types->includes( $post->post_type ) ) {
			return [];
		}

		$current_site_id = (int) get_current_blog_id();

		$related_site_ids = $this->site_relations->get_related_site_ids( $current_site_id, false );
		if ( ! $related_site_ids ) {
			return [];
		}

		$related_post_ids = $this->content_relations->get_relations( $current_site_id, $post->ID, 'post' );

		return array_map( function ( int $site_id ) use ( $related_post_ids ) {

			$related_post = empty( $related_post_ids[ $site_id ] )
				? null
				: get_blog_post( $site_id, $related_post_ids[ $site_id ] );

			return $this->create_meta_box_for_site( $site_id, $related_post );
		}, $related_site_ids );
	}

	/**
	 * @param \WP_Post      $post
	 * @param ServerRequest $request
	 *
	 * @return SourcePostSaveContext
	 */
	public function create_post_request_context( \WP_Post $post, ServerRequest $request ): SourcePostSaveContext {

		return new SourcePostSaveContext( $post, $this->active_post_types, $this->site_relations, $request );
	}

	/**
	 * Returns a post translation meta box controller according to the given site and post data.
	 *
	 * @param int      $site_id      Site ID.
	 * @param \WP_Post $related_post Optional. Related post object. Defaults to null.
	 *
	 * @return SiteAwareMetaBoxController Post translation meta box controller.
	 */
	private function create_meta_box_for_site( int $site_id, \WP_Post $related_post = null ): SiteAwareMetaBoxController {

		return new TranslationMetaBoxController(
			$site_id,
			$this->site_relations,
			$this->active_post_types,
			$related_post
		);
	}
}
