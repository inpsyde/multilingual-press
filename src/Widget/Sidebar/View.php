<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Sidebar;

/**
 * Interface for all sidebar widget view implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Sidebar
 * @since   3.0.0
 */
interface View {

	/**
	 * Renders the widget's front end view.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args     Widget arguments.
	 * @param array  $instance Widget settings.
	 * @param string $id_base  Widget ID base.
	 *
	 * @return void
	 */
	public function render( array $args, array $instance, $id_base );
}
