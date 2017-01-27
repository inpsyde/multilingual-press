<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Sidebar;

/**
 * Trait to be used by all self-registering widget implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Sidebar
 * @since   3.0.0
 */
trait SelfRegisteringWidget {

	/**
	 * Registers the widget.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the widget was registered successfully.
	 */
	public function register() {

		if ( did_action( 'widgets_init' ) ) {
			return false;
		}

		add_action( 'widgets_init', function () {

			register_widget( $this );
		} );

		return true;
	}
}
