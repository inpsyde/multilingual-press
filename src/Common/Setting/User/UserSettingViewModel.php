<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\User;

/**
 * Interface for all user setting view model implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\User
 * @since   3.0.0
 */
interface UserSettingViewModel {

	/**
	 * Renders the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_User $user User object.
	 *
	 * @return void
	 */
	public function render( \WP_User $user );

	/**
	 * Returns the title of the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the user setting.
	 */
	public function title(): string;
}
