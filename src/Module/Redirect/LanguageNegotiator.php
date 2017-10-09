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
	 * @param array $args Optional. Arguments required to determine the redirect targets. Defaults to empty array.
	 *
	 * @return RedirectTarget Redirect target object.
	 */
	public function get_redirect_target( array $args = [] ): RedirectTarget;
}
