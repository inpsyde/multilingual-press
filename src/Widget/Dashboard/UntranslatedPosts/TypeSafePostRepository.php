<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

/**
 * Type-safe untranslated posts repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
final class TypeSafePostRepository implements PostRepository {

	/**
	 * Checks if the post with the given ID has been translated.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Optional. Post ID. Defaults to 0.
	 *
	 * @return bool Whether or not the post with the given ID has been translated.
	 */
	public function is_post_translated( $post_id = 0 ) {

		$post_id = (int) ( $post_id ?: get_the_ID() );

		if ( get_post_meta( $post_id, PostRepository::META_KEY, true ) ) {
			return true;
		}

		if ( get_post_meta( $post_id, PostRepository::DEPRECATED_META_KEY, true ) ) {
			$this->update_deprecated_post_meta( $post_id );

			return true;
		}

		return false;
	}

	/**
	 * Updates the translation complete setting value for the post with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $value   Setting value to be set.
	 *
	 * @return bool Whether or not the translation complete setting value was updated successfully.
	 */
	public function update_post( $post_id, $value ) {

		return (bool) update_post_meta( $post_id, PostRepository::META_KEY, (bool) $value );
	}

	/**
	 * Updates the meta value for the given post (i.e., deletes the deprecated key and uses the correct one).
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	private function update_deprecated_post_meta( $post_id ) {

		if ( update_post_meta( $post_id, PostRepository::META_KEY, true ) ) {
			delete_post_meta( $post_id, PostRepository::DEPRECATED_META_KEY );
		}
	}
}
