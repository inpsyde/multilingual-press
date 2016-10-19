<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\UserAdminLanguage;

/**
 * Type-safe language repository implementation.
 *
 * @package Inpsyde\MultilingualPress\Module\UserAdminLanguage
 * @since   3.0.0
 */
final class TypeSafeLanguageRepository implements LanguageRepository {

	/**
	 * Returns the user language for the user with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id Optional. User ID. Defaults to 0.
	 *
	 * @return string The user language for the user with the given ID.
	 */
	public function get_user_language( $user_id = 0 ) {

		$user_language = get_user_meta( $user_id ?: get_current_user_id(), LanguageRepository::META_KEY, true );

		return $user_language
			? (string) $user_language
			: '';
	}
}
