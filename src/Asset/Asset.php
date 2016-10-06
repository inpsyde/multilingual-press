<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Interface for all asset implementations.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
interface Asset {

	/**
	 * Returns the dependencies.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The dependencies.
	 */
	public function dependencies();

	/**
	 * Returns the handle.
	 *
	 * @since 3.0.0
	 *
	 * @return string The handle.
	 */
	public function handle();

	/**
	 * Returns the file URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string The file URL.
	 */
	public function url();

	/**
	 * Returns the file version.
	 *
	 * @since 3.0.0
	 *
	 * @return string|null The file version.
	 */
	public function version();

	/**
	 * Returns the handle.
	 *
	 * @since 3.0.0
	 *
	 * @return string The handle.
	 */
	public function __toString();
}
