<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Interface for all language negotiator implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
interface LanguageNegotiator {

	/**
	 * Returns the redirect target data object for the best-matching language version.
	 *
	 * @since 3.0.0
	 *
	 * @return RedirectTarget Redirect target object.
	 */
	public function get_redirect_target();
}
