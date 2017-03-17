<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\User;

/**
 * Interface for all user setting updater implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\User
 * @since   3.0.0
 */
interface UserSettingUpdater {

	/**
	 * Updates the setting with the data in the request for the user with the given ID.
	 *
	 * @since   3.0.0
	 * @wp-hook profile_update
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool Whether or not the user setting was updated successfully.
	 */
	public function update( $user_id ): bool;
}
