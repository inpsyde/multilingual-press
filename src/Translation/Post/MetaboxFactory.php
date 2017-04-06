<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Translation\Metabox\SiteSpecificMetabox;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
class MetaboxFactory {

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var SiteSettingsRepository
	 */
	private $settings_repository;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param SiteRelations          $site_relations
	 * @param ContentRelations       $content_relations
	 * @param SiteSettingsRepository $settings_repository
	 */
	public function __construct(
		SiteRelations $site_relations,
		ContentRelations $content_relations,
		SiteSettingsRepository $settings_repository
	) {

		$this->site_relations = $site_relations;

		$this->content_relations = $content_relations;

		$this->settings_repository = $settings_repository;
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

		$current_site = (int) get_current_blog_id();

		$related_sites = $this->site_relations->get_related_site_ids( $current_site, false );

		if ( ! $related_sites ) {
			return [];
		}

		$linked_posts = $this->content_relations->get_relations( $current_site, $post->ID, 'post' );

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

		$remote_post = empty( $linked_posts[ $site_id ] )
			? null
			: get_blog_post( $site_id, $linked_posts[ $site_id ] );

		return new TranslationMetabox(
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

		/*
		 * @TODO Here would be better to use specific setting repository methods, e.g. get_site_language().
		 *      The issue is that in TypeSafeSiteSettingsRepository get_site_language() never
		 *      returns and empty value, but fallbacks to default lang (en_US) which is a bit dangerous here.
		 *      Consider to return an empty value from TypeSafeSiteSettingsRepository::get_site_language() so we
		 *      can use a custom fallback instead of a possibly misleading default language.
		 */

		if ( null === $this->settings ) {
			$this->settings = $this->settings_repository->get_settings();
		}

		if ( ! empty( $settings[ $site_id ]['text'] ) ) {
			return stripslashes( (string) $settings[ $site_id ]['text'] );
		}

		if ( ! empty( $settings[ $site_id ]['lang'] ) ) {
			return stripslashes( (string) $settings[ $site_id ]['lang'] );
		}

		return "({$site_id})";
	}
}