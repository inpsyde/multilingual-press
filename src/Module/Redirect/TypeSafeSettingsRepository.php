<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Type-safe settings repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class TypeSafeSettingsRepository implements SettingsRepository {

	/**
	 * Returns the redirect setting for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return bool The redirect setting for the site with the given ID.
	 */
	public function get_site_setting( int $site_id = 0 ): bool {

		return (bool) get_blog_option( $site_id ?: get_current_blog_id(), SettingsRepository::OPTION_SITE );
	}

	/**
	 * Returns the redirect setting for the user with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id Optional. User ID. Defaults to 0.
	 *
	 * @return bool The redirect setting for the user with the given ID.
	 */
	public function get_user_setting( int $user_id = 0 ): bool {

		return (bool) get_user_meta( $user_id ?: get_current_user_id(), SettingsRepository::META_KEY_USER );
	}
}
