<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Widget\Sidebar\View;

use function Inpsyde\MultilingualPress\get_linked_elements;

/**
 * Interface for all widget view implementations.
 *
 * @package Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher
 * @since   3.0.0
 */
final class WidgetView implements View {

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param AssetManager $asset_manager Asset manager object.
	 */
	public function __construct( AssetManager $asset_manager ) {

		$this->asset_manager = $asset_manager;
	}

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
	public function render( array $args, array $instance, string $id_base ) {

		$output = get_linked_elements( [
			'link_text'         => empty( $instance['widget_link_type'] ) ? 'text' : $instance['widget_link_type'],
			'show_current_blog' => ! empty( $instance['widget_show_current_blog'] ),
			'strict'            => ! empty( $instance['widget_toggle_view_on_translated_posts'] ),
		] );
		if ( ! $output ) {
			return;
		}

		$this->enqueue_style();

		echo $args['before_widget'] ?? '';

		if ( ! empty( $instance['widget_title'] ) ) {
			/** This filter is documented in wp-includes/default-widgets.php */
			$title = (string) apply_filters( 'widget_title', (string) $instance['widget_title'], $instance, $id_base );

			echo $args['before_title'] ?? '';
			echo esc_html( $title );
			echo $args['after_title'] ?? '';
		}

		echo $output;

		echo $args['after_widget'] ?? '';
	}

	/**
	 * Enqueues the front-end styles.
	 *
	 * @return void
	 */
	private function enqueue_style() {

		$theme_support = get_theme_support( 'multilingualpress' );
		if ( empty( $theme_support[0]['language_switcher_widget_style'] ) ) {
			$this->asset_manager->enqueue_style( 'multilingualpress' );
		}
	}
}
