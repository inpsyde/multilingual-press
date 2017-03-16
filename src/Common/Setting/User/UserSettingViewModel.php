<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\User;

use WP_User;

/**
 * Interface for all user setting view model implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\User
 * @since   3.0.0
 */
interface UserSettingViewModel {

	/**
	 * Returns the markup for the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_User $user User object.
	 *
	 * @return string The markup for the user setting.
	 */
	public function markup( WP_User $user ): string;

	/**
	 * Returns the title of the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the user setting.
	 */
	public function title(): string;
}
