<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Dashboard;

/**
 * Interface for all dashboard widget view implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard
 * @since   3.0.0
 */
interface View {

	/**
	 * Renders the widget's view.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function render( array $args, array $instance );
}
