<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Type-safe settings repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class TypeSafeSettingsRepository implements SettingsRepository {

	/**
	 * Returns the redirect setting for the user with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id Optional. User ID. Defaults to 0.
	 *
	 * @return bool The redirect setting for the user with the given ID.
	 */
	public function get_user_setting( $user_id = 0 ) {

		return (bool) get_user_meta( $user_id ?: get_current_user_id(), SettingsRepository::META_KEY_USER, true );
	}
}
