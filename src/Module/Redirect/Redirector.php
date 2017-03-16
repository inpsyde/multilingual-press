<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect;

/**
 * Interface for all redirector implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
interface Redirector {

	/**
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_URL = 'multilingualpress.redirect_url';

	/**
	 * Redirects the user to the best-matching language version, if any.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the user got redirected (for testing only).
	 */
	public function redirect(): bool;
}
