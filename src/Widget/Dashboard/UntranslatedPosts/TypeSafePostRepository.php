<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use WP_Post;

/**
 * Type-safe untranslated posts repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
final class TypeSafePostRepository implements PostRepository {

	/**
	 * Returns all untranslated posts for the current site.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Post[] All untranslated posts for the current site.
	 */
	public function get_untranslated_posts(): array {

		return get_posts( [
			// Not suppressing filters (which is done by default when using get_posts()) makes caching possible.
			'suppress_filters' => false,
			// Post status 'any' automatically excludes both 'auto-draft' and 'trash'.
			'post_status'      => 'any',
			'meta_query'       => [
				'relation' => 'OR',
				[
					'key'     => PostRepository::META_KEY,
					'compare' => '!=',
					'value'   => true,
				],
				[
					'key'     => PostRepository::DEPRECATED_META_KEY,
					'compare' => '!=',
					'value'   => true,
				],
			],
		] );
	}

	/**
	 * Checks if the post with the given ID has been translated.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Optional. Post ID. Defaults to 0.
	 *
	 * @return bool Whether or not the post with the given ID has been translated.
	 */
	public function is_post_translated( int $post_id = 0 ): bool {

		$post_id = $post_id ?: (int) get_the_ID();

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
	public function update_post( int $post_id, bool $value ): bool {

		return (bool) update_post_meta( $post_id, PostRepository::META_KEY, (bool) $value );
	}

	/**
	 * Updates the meta value for the given post (i.e., deletes the deprecated key and uses the correct one).
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	private function update_deprecated_post_meta( int $post_id ) {

		if ( update_post_meta( $post_id, PostRepository::META_KEY, true ) ) {
			delete_post_meta( $post_id, PostRepository::DEPRECATED_META_KEY );
		}
	}
}
