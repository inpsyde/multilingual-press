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
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_URL = 'multilingualpress.redirect_url';

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

	/**
	 * Returns the redirect target data objects for all available language versions.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Optional. Arguments required to determine the redirect targets. Defaults to empty array.
	 *
	 * @return RedirectTarget[] Array of redirect target objects.
	 */
	public function get_redirect_targets( array $args = [] ): array;
}
