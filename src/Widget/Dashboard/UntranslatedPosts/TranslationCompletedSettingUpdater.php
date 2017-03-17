<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use WP_Post;

/**
 * Translation completed setting updater.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
class TranslationCompletedSettingUpdater {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var PostRepository
	 */
	private $post_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PostRepository $post_repository Untranslated posts repository object.
	 * @param Nonce          $nonce           Nonce object.
	 */
	public function __construct( PostRepository $post_repository, Nonce $nonce ) {

		$this->post_repository = $post_repository;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the translation completed setting of the post with the given ID.
	 *
	 * @since   3.0.0
	 * @wp-hook save_post
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return bool Whether or not the translation completed setting was updated successfully.
	 */
	public function update_setting( $post_id, WP_Post $post ) {

		if ( ! $this->nonce->is_valid() )  {
			return false;
		}

		if ( ! in_array( $post->post_status, [ 'publish', 'draft' ], true ) ) {
			return false;
		}

		$value = ! empty( $_POST[ PostRepository::META_KEY ] );

		return $this->post_repository->update_post( (int) $post_id, $value );
	}
}
