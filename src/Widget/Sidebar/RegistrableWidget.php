<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Sidebar;

/**
 * Interface for all registrable widget implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Sidebar
 * @since   3.0.0
 */
interface RegistrableWidget {

	/**
	 * Registers the widget.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the widget was registered successfully.
	 */
	public function register();
}
