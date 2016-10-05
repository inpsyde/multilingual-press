<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\MultilingualPress;

// TODO: Refactor as soon as using a custom template renderer has been discussed.

// TODO: With WordPress 4.6 + 2, deprecate `widget_register()`, and constructor-inject `$asset_manager`.

/**
 * Language Switcher widget.
 */
class Mlp_Widget extends WP_Widget {

	/**
	 * Constructor. Sets up the properties.
	 */
	public function __construct() {

		parent::__construct( 'Mlp_Widget', __( 'Language Switcher', 'multilingual-press' ), [
			'classname'                   => 'mlp_widget',
			'description'                 => __( 'MultilingualPress Translations', 'multilingual-press' ),
			'customize_selective_refresh' => true,
		] );

		// Enqueue style if widget is active (appears in a sidebar) or if in Customizer preview.
		if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
			// Do NOT use wp_enqueue_scripts here as require_style() implicitly hooks into this.
			add_action( 'template_redirect', [ $this, 'require_style' ] );
		}
	}

	/**
	 * Registers the widget.
	 *
	 * @return void
	 */
	public static function widget_register() {

		register_widget( __CLASS__ );
	}

	/**
	 * Enqueues the frontend styles.
	 *
	 * @wp-hook template_redirect
	 *
	 * @return bool
	 */
	public function require_style() {

		$theme_support = get_theme_support( 'multilingualpress' );
		if ( ! empty( $theme_support[0]['language_switcher_widget_style'] ) ) {
			return false;
		}

		MultilingualPress::resolve( 'multilingualpress.asset_manager' )->enqueue_style( 'multilingualpress' );

		return true;
	}

	/**
	 * Renders the widget's admin view.
	 *
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {

		$title = isset( $instance['widget_title'] ) ? $instance['widget_title'] : '';

		$link_type = isset( $instance['widget_link_type'] ) ? $instance['widget_link_type'] : '';

		$display_flag = ! empty( $instance['widget_display_flag'] );

		$show_current_blog = ! empty( $instance['widget_show_current_blog'] );

		$show_widget = ! empty( $instance['widget_toggle_view_on_translated_posts'] );
		?>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_title' );

			$name = $this->get_field_name( 'mlp_widget_title' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Title', 'multilingual-press' ); ?></label><br>
			<input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $title ); ?>"
				class="widefat" id="<?php echo esc_attr( $id ); ?>">
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_link_type' );

			$name = $this->get_field_name( 'mlp_widget_link_type' );

			$options = [
				'none'           => __( 'None', 'multilingual-press' ),
				'native'         => __( 'Native name', 'multilingual-press' ),
				'text'           => __( 'Custom name', 'multilingual-press' ),
				'english'        => __( 'English name', 'multilingual-press' ),
				'http'           => __( 'Language code', 'multilingual-press' ),
				'language_short' => __( 'Language code (short)', 'multilingual-press' ),
			];
			?>
			<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Link text', 'multilingual-press' ); ?></label>
			<select name="<?php echo esc_attr( $name ); ?>" class="widefat" id="<?php echo esc_attr( $id ); ?>"
				autocomplete="off">
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

			$name = $this->get_field_name( 'mlp_widget_display_flag' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"<?php checked( $display_flag ); ?>>
				<?php _e( 'Show flag', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_show_current_blog' );

			$name = $this->get_field_name( 'mlp_widget_show_current_blog' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"<?php checked( $show_current_blog ); ?>>
				<?php _e( 'Show current site', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			$id = $this->get_field_id( 'mlp_widget_toggle_view_on_translated_posts' );

			$name = $this->get_field_name( 'mlp_widget_toggle_view_on_translated_posts' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>"<?php checked( $show_widget ); ?>>
				<?php _e( 'Show links for translated content only.', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php if ( current_user_can( 'manage_network_options' ) ) : ?>
				<?php
				printf(
					__( 'Languages are sorted by <a href="%s">priority</a>.', 'multilingual-press' ),
					network_admin_url( 'settings.php?page=language-manager' )
				);
				?>
			<?php else : ?>
				<?php _e( 'Languages are sorted by priority.', 'multilingual-press' ); ?>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Updates the widget settings.
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $instance     Current widget settings.
	 *
	 * @return array
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
	 * When a widget is restored from trash, the instance might be incomplete, hence the preparations.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$output = Mlp_Helpers::show_linked_elements( [
			'link_text'         => empty( $instance['widget_link_type'] ) ? 'text' : $instance['widget_link_type'],
			'show_current_blog' => ! empty( $instance['widget_show_current_blog'] ),
			'display_flag'      => ! empty( $instance['widget_display_flag'] ),
			'strict'            => ! empty( $instance['widget_toggle_view_on_translated_posts'] ),
		] );
		if ( ! $output ) {
			return;
		}

		$title = '';
		if ( ! empty( $instance['widget_title'] ) ) {
			$title = $instance['widget_title'];
		}
		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title );

		echo $args['before_widget'];

		if ( ! empty( $instance['widget_title'] ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		echo $output;

		echo $args['after_widget'];
	}
}
