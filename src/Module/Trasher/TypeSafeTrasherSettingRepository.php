<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Trasher;

/**
 * Type-safe trasher setting repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
 * @since   3.0.0
 */
final class TypeSafeTrasherSettingRepository implements TrasherSettingRepository {

	/**
	 * Returns the trasher setting value for the post with the given ID, or the current post.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Optional. Post ID. Defaults to 0.
	 *
	 * @return bool The trasher setting value for the post with the given ID, or the current post.
	 */
	public function get_setting( $post_id = 0 ) {

		return (bool) get_post_meta( $post_id ?: get_the_ID(), TrasherSettingRepository::META_KEY, true );
	}

	/**
	 * Updates the trasher setting value for the post with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Post ID.
	 * @param bool $value  Setting value to be set.
	 *
	 * @return bool Whether or not the trasher setting value was updated successfully.
	 */
	public function update_setting( $post_id, $value ) {

		return (bool) update_post_meta( $post_id, TrasherSettingRepository::META_KEY, (bool) $value );
	}
}
