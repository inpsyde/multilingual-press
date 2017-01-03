<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Quicklinks;

/**
 * Interface for all quicklinks settings repository implementations.
 *
 * @package Inpsyde\MultilingualPress\Module\Quicklinks
 * @since   3.0.0
 */
interface SettingsRepository {

	/**
	 * Option name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'inpsyde_multilingual_quicklink_options';

	/**
	 * Returns all available positions.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with position setting values as keys and position names as values.
	 */
	public function get_available_positions();

	/**
	 * Returns the currently selected position.
	 *
	 * @since 3.0.0
	 *
	 * @return string The currently selected position.
	 */
	public function get_current_position();

	/**
	 * Sets the position to the given value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $position Position value.
	 *
	 * @return bool Whether or not the position was set successfully.
	 */
	public function set_position( $position );
}
