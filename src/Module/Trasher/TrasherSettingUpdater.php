<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
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
	 * @var Nonce
	 */
	private $nonce;

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
	 * @param Nonce                    $nonce              Nonce object.
	 */
	public function __construct(
		TrasherSettingRepository $setting_repository,
		ContentRelations $content_relations,
		Nonce $nonce
	) {

		$this->setting_repository = $setting_repository;

		$this->content_relations = $content_relations;

		$this->nonce = $nonce;
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

		if ( ! $this->nonce->is_valid() )  {
			return 0;
		}

		if ( ! in_array( $post->post_status, [ 'publish', 'draft' ], true ) ) {
			return 0;
		}

		$value = array_key_exists( TrasherSettingRepository::META_KEY, $_POST )
			? (bool) $_POST[ TrasherSettingRepository::META_KEY ]
			: false;

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
