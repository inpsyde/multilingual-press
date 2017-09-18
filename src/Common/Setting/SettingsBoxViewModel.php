<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting;

/**
 * Interface for all settings box view model implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting
 * @since   3.0.0
 */
interface SettingsBoxViewModel {

	/**
	 * Returns the description.
	 *
	 * @since 3.0.0
	 *
	 * @return string The description.
	 */
	public function description(): string;

	/**
	 * Returns the ID of the container element.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID of the container element.
	 */
	public function id(): string;

	/**
	 * Returns the ID of the form element to be used by the label in order to make it accessible for screen readers.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID of the primary form element.
	 */
	public function label_id(): string;

	/**
	 * Renders the markup for the settings box.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render();

	/**
	 * Returns the title of the settings box.
	 *
	 * @since 3.0.0
	 *
	 * @return string The title of the settings box.
	 */
	public function title(): string;
}
