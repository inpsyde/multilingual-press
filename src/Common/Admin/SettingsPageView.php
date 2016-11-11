<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Interface for all settings page view implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
interface SettingsPageView {

	/**
	 * Renders the markup.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render();
}
