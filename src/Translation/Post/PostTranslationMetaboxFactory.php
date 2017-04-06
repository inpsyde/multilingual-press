<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Translation\Metabox\SiteSpecificMetabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
class PostTranslationMetaboxFactory {

	const BLOGS_OPTION_NAME = 'inpsyde_multilingual';

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	private $blogs;

	/**
	 * Constructor.
	 *
	 * @param SiteRelations    $site_relations
	 * @param ContentRelations $content_relations
	 */
	public function __construct( SiteRelations $site_relations, ContentRelations $content_relations ) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;

		$this->blogs = (array) get_site_option( self::BLOGS_OPTION_NAME, [] ) ?: [];
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return array|SiteSpecificMetabox[]
	 */
	public function create_boxes( \WP_Post $post ): array {

		$post_types = $this->allowed_post_types();
		if ( ! $post_types || ! in_array( $post->post_type, $post_types, true ) ) {
			return [];
		}

		$related_sites = $this->site_relations->get_related_site_ids( (int) get_current_blog_id(), false );

		if ( ! $related_sites ) {
			return [];
		}

		$linked_posts = $this->content_relations->get_relations(
			get_current_blog_id(),
			$post->ID,
			'post'
		);

		return array_map( function ( $site_id ) use ( $linked_posts, $post_types ) {

			return $this->create_site_box( (int) $site_id, $linked_posts, $post_types );

		}, $related_sites );
	}

	/**
	 * @param int   $site_id
	 * @param array $linked_posts
	 * @param array $post_types
	 *
	 * @return SiteSpecificMetabox
	 */
	private function create_site_box( int $site_id, array $linked_posts, array $post_types ): SiteSpecificMetabox {

		$remote_post = empty( $linked_posts[ $site_id ] ) ? null : get_blog_post( $site_id, $linked_posts[ $site_id ] );

		return new PostTranslationMetabox(
			$site_id,
			$this->language_for_site( $site_id ),
			$post_types,
			$remote_post
		);
	}

	/**
	 * @return array
	 */
	private function allowed_post_types() {

		/**
		 * Filter the allowed post types.
		 *
		 * @param string[] $allowed_post_types Allowed post type names.
		 */
		return (array) apply_filters( 'multilingualpress.allowed_post_types', [ 'post', 'page' ] );
	}

	/**
	 * @param  int $site_id
	 *
	 * @return string
	 */
	public function language_for_site( $site_id ) {

		$language = '(' . $site_id . ')';

		if ( empty( $this->blogs[ $site_id ] ) ) {
			return $language;
		}

		$data = $this->blogs[ $site_id ];

		if ( ! empty( $data['text'] ) ) {
			return $data['text'];
		}

		if ( ! empty( $data['lang'] ) ) {
			return $data['lang'];
		}

		return $language;
	}
}