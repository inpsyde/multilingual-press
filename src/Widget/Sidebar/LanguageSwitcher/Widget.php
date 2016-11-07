<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher;

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\MultilingualPress;
use Inpsyde\MultilingualPress\Widget\Sidebar\View;
use WP_Widget;

/**
 * Language switcher widget.
 *
 * @package Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher
 * @since   3.0.0
 */
final class Widget extends WP_Widget {

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * @var View
	 */
	private $view;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct( 'Mlp_Widget', __( 'Language Switcher', 'multilingual-press' ), [
			'classname'                   => 'mlp_widget',
			'description'                 => __( 'MultilingualPress Translations', 'multilingual-press' ),
			'customize_selective_refresh' => true,
		] );

		// TODO: With WordPress 4.6 + 2, inject an asset manager instance.
		if ( ! isset( $this->asset_manager ) ) {
			$this->asset_manager = MultilingualPress::resolve( 'multilingualpress.asset_manager' );
		}

		// TODO: With WordPress 4.6 + 2, inject a view instance.
		if ( ! isset( $this->view ) ) {
			$this->view = new WidgetView();
		}

		// Enqueue style if front end and widget is active (ei.e., it appears in a sidebar) or if in Customizer preview.
		if ( ( ! is_admin() && is_active_widget( false, false, $this->id_base ) ) || is_customize_preview() ) {
			$this->enqueue_style();
		}
	}

	/**
	 * Renders the widget's admin view.
	 *
	 * @since 3.0.0
	 *
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {

		?>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_title' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Title', 'multilingual-press' ); ?></label><br>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'mlp_widget_title' ) ); ?>"
				value="<?php echo esc_attr( isset( $instance['widget_title'] ) ? $instance['widget_title'] : '' ); ?>"
				class="widefat" id="<?php echo esc_attr( $id ); ?>">
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_link_type' );

			$options = [
				'none'           => __( 'None', 'multilingual-press' ),
				'native'         => __( 'Native name', 'multilingual-press' ),
				'text'           => __( 'Custom name', 'multilingual-press' ),
				'english'        => __( 'English name', 'multilingual-press' ),
				'http'           => __( 'Language code', 'multilingual-press' ),
				'language_short' => __( 'Language code (short)', 'multilingual-press' ),
			];

			$link_type = isset( $instance['widget_link_type'] ) ? $instance['widget_link_type'] : '';
			?>
			<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Link text', 'multilingual-press' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'mlp_widget_link_type' ) ); ?>" class="widefat"
				id="<?php echo esc_attr( $id ); ?>" autocomplete="off">
				<?php foreach ( $options as $value => $text ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $link_type ); ?>>
						<?php echo esc_html( $text ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_display_flag' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
					name="<?php echo esc_attr( $this->get_field_name( 'mlp_widget_display_flag' ) ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"
					<?php checked( ! empty( $instance['widget_display_flag'] ) ); ?>>
				<?php _e( 'Show flag', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_show_current_blog' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
					name="<?php echo esc_attr( $this->get_field_name( 'mlp_widget_show_current_blog' ) ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"
					<?php checked( ! empty( $instance['widget_show_current_blog'] ) ); ?>>
				<?php _e( 'Show current site', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_toggle_view_on_translated_posts' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
					name="<?php echo esc_attr( $this->get_field_name( 'mlp_widget_toggle_view_on_translated_posts' ) ); ?>"
					value="1" id="<?php echo esc_attr( $id ); ?>"
					<?php checked( ! empty( $instance['widget_toggle_view_on_translated_posts'] ) ); ?>>
				<?php _e( 'Show links for translated content only.', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			// TODO: Don't hard-code settings page capability.
			if ( current_user_can( 'manage_network_options' ) ) {
				printf(
					__( 'Languages are sorted by <a href="%s">priority</a>.', 'multilingual-press' ),
					// TODO: Don't hard-code settings page URL/slug.
					network_admin_url( 'settings.php?page=language-manager' )
				);
			} else {
				_e( 'Languages are sorted by priority.', 'multilingual-press' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Updates the widget settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $instance     Current widget settings.
	 *
	 * @return array Update widget settings.
	 */
	public function update( $new_instance, $instance ) {

		$instance['widget_title'] = esc_html( $new_instance['mlp_widget_title'] );

		$instance['widget_link_type'] = esc_attr( $new_instance['mlp_widget_link_type'] );

		$instance['widget_display_flag'] = (int) (
			isset( $new_instance['mlp_widget_display_flag'] )
			&& '1' === $new_instance['mlp_widget_display_flag']
		);

		$instance['widget_show_current_blog'] = (int) (
			isset( $new_instance['mlp_widget_show_current_blog'] )
			&& '1' === $new_instance['mlp_widget_show_current_blog']
		);

		$instance['widget_toggle_view_on_translated_posts'] = (int) (
			isset( $new_instance['mlp_widget_toggle_view_on_translated_posts'] )
			&& '1' === $new_instance['mlp_widget_toggle_view_on_translated_posts']
		);

		return $instance;
	}

	/**
	 * Renders the widget's front end view.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$this->view->render( (array) $args, (array) $instance );
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
