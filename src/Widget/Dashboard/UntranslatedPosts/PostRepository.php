<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

/**
 * Interface for all untranslated posts repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
interface PostRepository {

	/**
	 * Meta key used to store the translation status.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const META_KEY = '_post_is_translated';

	/**
	 * Returns all untranslated posts for the current site.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Post[] All untranslated posts for the current site.
	 */
	public function get_untranslated_posts(): array;

	/**
	 * Checks if the post with the given ID has been translated.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Optional. Post ID. Defaults to 0.
	 *
	 * @return bool Whether or not the post with the given ID has been translated.
	 */
	public function is_post_translated( int $post_id = 0 ): bool;

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
	public function update_post( int $post_id, bool $value ): bool;
}
