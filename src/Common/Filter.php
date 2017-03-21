<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Interface for all filter implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface Filter {

	/**
	 * Default value.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const DEFAULT_ACCEPTED_ARGS = 1;

	/**
	 * Default value.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const DEFAULT_PRIORITY = 10;

	/**
	 * Returns the number of accepted arguments.
	 *
	 * @since 3.0.0
	 *
	 * @return int The number of accepted arguments.
	 */
	public function accepted_args(): int;

	/**
	 * Removes the filter.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $hook     Optional. Hook name. Defaults to empty string.
	 * @param int|null $priority Optional. Callback priority. Defaults to null.
	 *
	 * @return bool Whether or not the filter was removed successfully.
	 */
	public function disable( string $hook = '', $priority = null ): bool;

	/**
	 * Adds the filter.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $hook          Optional. Hook name. Defaults to empty string.
	 * @param int|null $priority      Optional. Callback priority. Defaults to null.
	 * @param int|null $accepted_args Optional. Number of accepted arguments. Defaults to null.
	 *
	 * @return bool Whether or not the filter was added successfully.
	 */
	public function enable( string $hook = '', $priority = null, $accepted_args = null ): bool;

	/**
	 * Returns the hook name.
	 *
	 * @since 3.0.0
	 *
	 * @return string The hook name.
	 */
	public function hook(): string;

	/**
	 * Returns the callback priority.
	 *
	 * @since 3.0.0
	 *
	 * @return int The callback priority.
	 */
	public function priority(): int;
}
