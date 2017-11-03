<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Inteface for all settings page tab data implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
interface SettingsPageTabDataAccess {

	/**
	 * Returns the capability.
	 *
	 * @since 3.0.0
	 *
	 * @return string The capability.
	 */
	public function capability(): string;

	/**
	 * Returns the ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID.
	 */
	public function id(): string;

	/**
	 * Returns the slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string The slug.
	 */
	public function slug(): string;

	/**
	 * Returns the title.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title.
	 */
	public function title(): string;
}
