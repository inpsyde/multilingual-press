<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard;

/**
 * Trait to be used by all configurable dashboard widgets.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard
 * @since   3.0.0
 */
trait DashboardWidgetOptions {

	/**
	 * Returns the options for the widget with the given ID.
	 *
	 * @param string $widget_id Widget ID.
	 *
	 * @return array Widget options.
	 */
	private function get_options( string $widget_id ): array {

		$options = (array) get_option( 'dashboard_widget_options' ) ?: [];

		return $options[ $widget_id ] ?? [];
	}

	/**
	 * Returns a specific widget option, if available.
	 *
	 * @param string $widget_id Widget ID.
	 * @param string $name      Option name.
	 * @param mixed  $default   Optional. Default value. Defaults to null.
	 *
	 * @return mixed Option value.
	 */
	private function get_option( string $widget_id, string $name, $default = null ) {

		$options = $this->get_options( $widget_id );

		return $options[ $name ] ?? $default;
	}

	/**
	 * Saves an array of options for the widget with the given ID.
	 *
	 * @param string $widget_id Widget ID.
	 * @param array  $args      Optional. Associative array of options to be saved. Defaults to empty array.
	 *
	 * @return bool Whether or not the options have been saved successfully.
	 */
	private function update_options( string $widget_id, array $args = [] ): bool {

		$options = get_option( 'dashboard_widget_options' );

		$options[ $widget_id ] = array_merge( (array) $options[ $widget_id ] ?? [], $args );

		return update_option( 'dashboard_widget_options', $options );
	}

	/**
	 * Saves a specific widget option.
	 *
	 * @param string $widget_id Widget ID.
	 * @param string $name      Option name.
	 * @param mixed  $value     Option value.
	 *
	 * @return bool Whether or not the option has been saved successfully.
	 */
	private function update_option( string $widget_id, string $name, $value ): bool {

		$options = get_option( 'dashboard_widget_options' );

		$widget_options = (array) $options[ $widget_id ] ?? [];

		$widget_options[ $name ] = $value;

		$options[ $widget_id ] = $widget_options;

		return update_option( 'dashboard_widget_options', $options );
	}
}
