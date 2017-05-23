<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Common\HTTP\Request;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

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
	 * @var PostsRepository
	 */
	private $posts_repository;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PostsRepository $posts_repository Untranslated posts repository object.
	 * @param Request         $request          HTTP request object.
	 * @param Nonce           $nonce            Nonce object.
	 */
	public function __construct( PostsRepository $posts_repository, Request $request, Nonce $nonce ) {

		$this->posts_repository = $posts_repository;

		$this->request = $request;

		$this->nonce = $nonce;
	}

	/**
	 * Updates the translation completed setting of the post with the given ID.
	 *
	 * @since   3.0.0
	 * @wp-hook save_post
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return bool Whether or not the translation completed setting was updated successfully.
	 */
	public function update_setting( $post_id, \WP_Post $post ) {

		if ( ! $this->nonce->is_valid() ) {
			return false;
		}

		if ( ! in_array( $post->post_status, [ 'publish', 'draft' ], true ) ) {
			return false;
		}

		$value = (bool) $this->request->body_value( PostsRepository::META_KEY, INPUT_POST, FILTER_VALIDATE_BOOLEAN );

		return $this->posts_repository->update_post( (int) $post_id, $value );
	}
}
