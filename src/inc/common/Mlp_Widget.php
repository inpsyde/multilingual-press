<?php

/**
 * Language switcher widget
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2015.06.26
 * @license GPL
 */
class Mlp_Widget extends WP_Widget {

	/**
	 * @var string
	 */
	protected static $handle = 'mlp_widget';

	/**
	 * @var Mlp_Assets_Interface
	 */
	private static $assets;

	/**
	 * Register the widget and set up the description.
	 */
	public function __construct() {

		add_action( 'template_redirect', array( $this, 'require_style' ) );

		$widget_ops = array(
			'classname'   => self::$handle,
			'description' => __( 'MultilingualPress Translations', 'multilingual-press' ),
		);
		parent::__construct( 'Mlp_Widget', __( 'Language Switcher', 'multilingual-press' ), $widget_ops );
	}

	/**
	 * Load frontend CSS if the widget is active.
	 *
	 * @wp-hook template_redirect
	 *
	 * @return bool
	 */
	public function require_style() {

		if ( ! is_active_widget( false, false, self::$handle ) ) {
			return false;
		}

		$theme_support = get_theme_support( 'multilingualpress' );
		if ( ! empty( $theme_support[0]['language_switcher_widget_style'] ) ) {
			return false;
		}

		self::$assets->provide( 'mlp_frontend_css' );

		return true;
	}

	/**
	 * Display widget admin form.
	 *
	 * @param array $instance Widget settings
	 *
	 * @return void
	 */
	public function form( $instance ) {

		$instance = $this->adapt_settings( $instance );

		$title             = isset( $instance['widget_title'] )
			? esc_attr( $instance['widget_title'] ) : '';
		$link_type         = isset( $instance['widget_title'] )
			? esc_attr( $instance['widget_link_type'] ) : '';
		$show_current_blog = isset( $instance['widget_show_current_blog'] )
			? strip_tags( $instance['widget_show_current_blog'] ) : '';
		$display_flag      = isset( $instance['widget_display_flag'] )
			? strip_tags( $instance['widget_display_flag'] ) : '';
		$show_widget       = isset( $instance['widget_toggle_view_on_translated_posts'] )
			? strip_tags( $instance['widget_toggle_view_on_translated_posts'] ) : '';
		?>
		<p>
			<?php
			$id   = $this->get_field_id( 'mlp_widget_title' );
			$name = $this->get_field_name( 'mlp_widget_title' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Title', 'multilingual-press' ); ?></label><br>
			<input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $title ); ?>"
				class="widefat" id="<?php echo esc_attr( $id ); ?>">
		</p>
		<p>
			<?php
			$id   = $this->get_field_id( 'mlp_widget_link_type' );
			$name = $this->get_field_name( 'mlp_widget_link_type' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Link text', 'multilingual-press' ); ?></label>
			<select name="<?php echo esc_attr( $name ); ?>" class="widefat" id="<?php echo esc_attr( $id ); ?>"
				autocomplete="off">
				<?php
				$options = array(
					'none'           => __( 'None', 'multilingual-press' ),
					'native'         => __( 'Native name', 'multilingual-press' ),
					'text'           => __( 'Custom name', 'multilingual-press' ),
					'english'        => __( 'English name', 'multilingual-press' ),
					'http'           => __( 'Language code', 'multilingual-press' ),
					'language_short' => __( 'Language code (short)', 'multilingual-press' ),
				);
				foreach ( $options as $value => $text ) {
					printf(
						'<option value="%1$s"%2$s>%3$s</option>',
						esc_attr( $value ),
						selected( $link_type, $value, false ),
						esc_html( $text )
					);
				}
				?>
			</select>
		</p>
		<p>
			<?php
			$id   = $this->get_field_id( 'mlp_widget_display_flag' );
			$name = $this->get_field_name( 'mlp_widget_display_flag' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
					name="<?php echo esc_attr( $name ); ?>" value="1" id="<?php echo esc_attr( $id ); ?>"
					<?php checked( $display_flag, 1 ); ?>>
				<?php _e( 'Show flag', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			$id   = $this->get_field_id( 'mlp_widget_show_current_blog' );
			$name = $this->get_field_name( 'mlp_widget_show_current_blog' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
					name="<?php echo esc_attr( $name ); ?>" value="1" id="<?php echo esc_attr( $id ); ?>"
					<?php checked( $show_current_blog, 1 ); ?>>
				<?php _e( 'Show current site', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			$id   = $this->get_field_id( 'mlp_widget_toggle_view_on_translated_posts' );
			$name = $this->get_field_name( 'mlp_widget_toggle_view_on_translated_posts' );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
					value="1"<?php checked( $show_widget, 1 ); ?>>
				<?php _e( 'Show links for translated content only.', 'multilingual-press' ); ?>
			</label>
		</p>
		<p>
			<?php
			if ( current_user_can( 'manage_network_options' ) ) {
				echo sprintf(
					__( 'Languages are sorted by <a href="%s">priority</a>.', 'multilingual-press' ),
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
	 * Callback for widget update.
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $old_instance Widget settings.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['widget_title']                           = esc_html( $new_instance['mlp_widget_title'] );
		$instance['widget_link_type']                       = esc_attr( $new_instance['mlp_widget_link_type'] );
		$instance['widget_show_current_blog']               = (int) (
			isset( $new_instance['mlp_widget_show_current_blog'] )
			&& $new_instance['mlp_widget_show_current_blog'] === '1'
		);
		$instance['widget_display_flag']                    = (int) (
			isset( $new_instance['mlp_widget_display_flag'] )
			&& $new_instance['mlp_widget_display_flag'] === '1'
		);
		$instance['widget_toggle_view_on_translated_posts'] = (int) (
			isset( $new_instance['mlp_widget_toggle_view_on_translated_posts'] )
			&& $new_instance['mlp_widget_toggle_view_on_translated_posts'] === '1'
		);

		return $instance;
	}

	/**
	 * Frontend display.
	 *
	 * When a widget is restored from trash, the instance might be incomplete, hence the preparations.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$instance = $this->adapt_settings( $instance );

		$link_type = 'text';
		if ( ! empty( $instance['widget_link_type'] ) ) {
			$link_type = $instance['widget_link_type'];
		}

		$display_flag = false;
		if ( ! empty( $instance['widget_display_flag'] ) ) {
			$display_flag = $instance['widget_display_flag'];
		}

		$show_current = true;
		if ( isset ( $instance['widget_show_current_blog'] ) ) {
			$show_current = (int) $instance['widget_show_current_blog'] === 1;
		}

		$output_args = array(
			'link_text'         => $link_type,
			'show_current_blog' => $show_current,
			'display_flag'      => $display_flag,
		);
		$output      = Mlp_Helpers::show_linked_elements( $output_args );
		if ( ! $output ) {
			return;
		}

		$title = '';
		if ( isset( $instance['widget_title'] ) ) {
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

	/**
	 * Adapt the internal settings to changes introduced in MultilingualPress 2.2.0.
	 *
	 * @see  https://github.com/inpsyde/multilingual-press/issues/112
	 *
	 * @todo Eventually remove this, with version 2.6.0 at the earliest.
	 *
	 * @param array $instance Widget settings.
	 *
	 * @return array
	 */
	private function adapt_settings( array $instance ) {

		$settings = $this->get_settings();

		if ( empty( $settings[ $this->number ] ) ) {
			// This should not happen (if it does, there's something wrong with WP_Widget)
			return $instance;
		}

		$instance = $settings[ $this->number ];

		if ( empty( $instance['widget_link_type'] ) ) {
			// No need to adapt anything
			return $instance;
		}

		switch ( $instance['widget_link_type'] ) {
			case 'text_flag':
				$instance['widget_link_type']    = 'native';
				$instance['widget_display_flag'] = true;
				break;

			case 'flag':
				$instance['widget_link_type']    = 'none';
				$instance['widget_display_flag'] = true;
				break;

			default:
				// No need to adapt anything
				return $instance;
		}

		$settings[ $this->number ] = $instance;

		$this->save_settings( $settings );

		return $instance;
	}

	/**
	 * Register the widget.
	 *
	 * @return void
	 */
	public static function widget_register() {

		register_widget( __CLASS__ );
	}

	/**
	 * Insert assets.
	 *
	 * @param Mlp_Assets_Interface $assets Assets.
	 *
	 * @return void
	 */
	public static function insert_asset_instance( Mlp_Assets_Interface $assets ) {

		self::$assets = $assets;
	}
}
