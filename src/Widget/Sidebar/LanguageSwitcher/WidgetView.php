<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher;

use Inpsyde\MultilingualPress\Widget\Sidebar\View;

/**
 * Interrface for all widget view implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher
 * @since   3.0.0
 */
final class WidgetView implements View {

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
	public function render( array $args, array $instance, $id_base ) {

		$output = \Inpsyde\MultilingualPress\get_linked_elements( [
			'link_text'         => empty( $instance['widget_link_type'] ) ? 'text' : $instance['widget_link_type'],
			'show_current_blog' => ! empty( $instance['widget_show_current_blog'] ),
			'display_flag'      => ! empty( $instance['widget_display_flag'] ),
			'strict'            => ! empty( $instance['widget_toggle_view_on_translated_posts'] ),
		] );
		if ( ! $output ) {
			return;
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['widget_title'] ) ) {
			/** This filter is documented in wp-includes/default-widgets.php */
			$title = (string) apply_filters( 'widget_title', (string) $instance['widget_title'], $instance, $id_base );

			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		echo $output;

		echo $args['after_widget'];
	}
}
