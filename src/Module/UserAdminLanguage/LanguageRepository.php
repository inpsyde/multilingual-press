<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\UserAdminLanguage;

/**
 * Interface for all language repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\UserAdminLanguage
 * @since   3.0.0
 */
interface LanguageRepository {

	/**
	 * Meta key for storing the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const META_KEY = 'mlp_user_language';

	/**
	 * Returns the user language for the user with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id Optional. User ID. Defaults to 0.
	 *
	 * @return string The user language for the user with the given ID.
	 */
	public function get_user_language( $user_id = 0 );
}
