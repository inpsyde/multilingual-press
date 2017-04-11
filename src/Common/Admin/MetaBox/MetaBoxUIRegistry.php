<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box UI registry implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBoxUIRegistry {

	/**
	 * Returns an array with all meta box IDs.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with all meta box IDs.
	 */
	public function get_ids(): array;

	/**
	 * Returns an array with all meta box names.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with all meta box IDs as keys and the names as values.
	 */
	public function get_names(): array;

	/**
	 * Returns an array with all meta box objects.
	 *
	 * @since 3.0.0
	 *
	 * @return MetaBoxUI[] An array with all meta box IDs as keys and the objects as values.
	 */
	public function get_objects(): array;

	/**
	 * Registers both the meta box view and the metadata updater of the selected UI for usage.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the registration was successful.
	 */
	public function register(): bool;

	/**
	 * Registers the given meta box UI.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxUI $ui UI object.
	 *
	 * @return MetaBoxUIRegistry
	 */
	public function register_ui( MetaBoxUI $ui ): MetaBoxUIRegistry;
}
