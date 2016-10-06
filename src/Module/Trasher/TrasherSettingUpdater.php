<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\API\ContentRelations;
use WP_Post;

/**
 * Trasher setting updater.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
 * @since   3.0.0
 */
class TrasherSettingUpdater {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var TrasherSettingRepository
	 */
	private $setting_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TrasherSettingRepository $setting_repository Trasher setting repository object.
	 * @param ContentRelations         $content_relations  Content relations API object.
	 */
	public function __construct( TrasherSettingRepository $setting_repository, ContentRelations $content_relations ) {

		$this->setting_repository = $setting_repository;

		$this->content_relations = $content_relations;
	}

	/**
	 * Updates the trasher setting of the post with the given ID as well as all related posts.
	 *
	 * @since   3.0.0
	 * @wp-hook save_post
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_post $post    Post object.
	 *
	 * @return int The number of posts updated.
	 */
	public function update_settings( $post_id, WP_Post $post ) {

		if ( ! in_array( $post->post_status, [ 'publish', 'draft' ], true ) ) {
			return 0;
		}

		$meta_key = TrasherSettingRepository::META_KEY;
		if ( ! array_key_exists( $meta_key, $_POST ) ) {
			return 0;
		}

		// TODO: Use a nonce here!

		$value = (bool) $_POST[ $meta_key ];

		if ( ! $this->setting_repository->update( $post_id, $value ) ) {
			return 0;
		}

		$current_site_id = get_current_blog_id();

		$related_posts = $this->content_relations->get_relations( $current_site_id, $post_id, 'post' );

		unset( $related_posts[ $current_site_id ] );

		if ( ! $related_posts ) {
			return 1;
		}

		$updated_posts = 1;

		array_walk( $related_posts, function ( $post_id, $site_id ) use ( &$updated_posts, $value ) {

			switch_to_blog( $site_id );
			$updated_posts += $this->setting_repository->update( $post_id, $value );
			restore_current_blog();
		} );

		return $updated_posts;
	}
}
