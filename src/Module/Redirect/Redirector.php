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
	 * Hook name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_TYPE = 'multilingualpress.redirector_type';

	/**
	 * Redirector type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_JAVASCRIPT = 'JAVASCRIPT';

	/**
	 * Redirector type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_PHP = 'PHP';

	/**
	 * Redirects the user to the best-matching language version, if any.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the user got redirected (for testing only).
	 */
	public function redirect(): bool;
}
