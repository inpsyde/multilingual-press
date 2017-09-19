<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\NetworkState;
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
	 * @var ActivePostTypes
	 */
	private $active_post_types;

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
	 * @param ActivePostTypes  $active_post_types Active post types object.
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

		$current_site_id = get_current_blog_id();

		$related_site_ids = $this->site_relations->get_related_site_ids( $current_site_id, false );
		if ( ! $related_site_ids ) {
			return [];
		}

		$relations = $this->content_relations->get_relations(
			$current_site_id,
			(int) $post->ID,
			ContentRelations::CONTENT_TYPE_POST
		);

		$controllers = [];

		$state = NetworkState::create();

		foreach ( $related_site_ids as $site_id ) {
			switch_to_blog( $site_id );

			if ( ! post_type_exists( $post->post_type ) ) {
				continue;
			}

			$related_post = empty( $relations[ $site_id ] )
				? null
				: get_post( $relations[ $site_id ] );

			$controllers[] = $this->create_meta_box_for_site( $site_id, $related_post );
		}

		$state->restore();

		return $controllers;
	}

	/**
	 * @param \WP_Post      $post    Post object.
	 * @param ServerRequest $request HTTP server request object.
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
	private function create_meta_box_for_site(
		int $site_id,
		\WP_Post $related_post = null
	): SiteAwareMetaBoxController {

		return new TranslationMetaBoxController(
			$site_id,
			$this->site_relations,
			$this->active_post_types,
			$related_post
		);
	}
}
