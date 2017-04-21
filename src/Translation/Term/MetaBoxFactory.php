<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\SiteAwareMetaBoxController;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\NetworkState;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\SourceTermSaveContext;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\TranslationMetaBoxController;

/**
 * Factory for term translation meta box controller objects.
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
	 * @var ActiveTaxonomies
	 */
	private $active_taxonomies;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteRelations    $site_relations    Site relations API object.
	 * @param ContentRelations $content_relations Content relations API object.
	 * @param ActiveTaxonomies  $active_taxonomies Active taxonomies object.
	 */
	public function __construct(
		SiteRelations $site_relations,
		ContentRelations $content_relations,
		ActiveTaxonomies $active_taxonomies
	) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;

		$this->active_taxonomies = $active_taxonomies;
	}

	/**
	 * Returns an array with all post translation meta box controllers for the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term $term Term object.
	 *
	 * @return SiteAwareMetaBoxController[] Post translation meta box controllers.
	 */
	public function create_meta_boxes( \WP_Term $term ): array {

		$current_site_id = (int) get_current_blog_id();

		$related_site_ids = $this->site_relations->get_related_site_ids( $current_site_id, false );
		if ( ! $related_site_ids ) {
			return [];
		}

		$related_post_ids = $this->content_relations->get_relations( $current_site_id, $term->term_id, 'term' );

		$controllers = [];

		$state = NetworkState::from_globals();

		foreach ( $related_post_ids as $site_id ) {

			switch_to_blog( $site_id );

			if ( ! taxonomy_exists( $term->taxonomy ) ) {
				continue;
			}

			$related_term = empty( $related_post_ids[ $site_id ] )
				? null
				: get_term( $related_post_ids[ $site_id ], $term->taxonomy );

			$controllers[] = $this->create_meta_box_for_site( $site_id, $related_term );
		}

		$state->restore();

		return $controllers;
	}

	/**
	 * @param \WP_Term      $term
	 * @param ServerRequest $request
	 *
	 * @return SourceTermSaveContext
	 */
	public function create_term_request_context( \WP_Term $term, ServerRequest $request ): SourceTermSaveContext {

		return new SourceTermSaveContext( $term, $this->active_taxonomies, $this->site_relations, $request );
	}

	/**
	 * Returns a post translation meta box controller according to the given site and post data.
	 *
	 * @param int      $site_id      Site ID.
	 * @param \WP_Term $related_term Optional. Related term object. Defaults to null.
	 *
	 * @return SiteAwareMetaBoxController Post translation meta box controller.
	 */
	private function create_meta_box_for_site( int $site_id, \WP_Term $related_term = null ): SiteAwareMetaBoxController {

		return new TranslationMetaBoxController(
			$site_id,
			$this->site_relations,
			$this->active_taxonomies,
			$related_term
		);
	}
}
