<?php
/**
 * Module Name:	Multilingual Press Widget
 * Description:	This Widget shows the flags
 * Author:		Inpsyde GmbH
 * Version:		0.3
 * Author URI:	http://inpsyde.com
 *
 * Changelog
 *
 * 0.3
 * - Added Sort order
 *
 * 0.2
 * - Codexified
 *
 * 0.1
 * - Initial Commit
 *
 */
class Mlp_Widget extends WP_Widget {

	/**
	 * Registers the widget and set up the description
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	Multilingual_Press, __
	 * @return	void
	 */
	public function mlp_widget() {

		$mlp = Multilingual_Press::get_object();

		$widget_ops = array(
			'classname'		=> 'Mlp_Widget',
			'description'	=> __( 'Multilingual Press Widget', 'multilingualpress' )
		);

		$this->WP_Widget( 'Mlp_Widget', __( 'Multilingual Press Widget', 'multilingualpress' ), $widget_ops );
	}

	/**
	 * Display widget admin form
	 *
	 * @since	0.1
	 * @access	public
	 * @param	array $instance | widget settings
	 * @uses	strip_tags, esc_attr, _e
	 * @return	void
	 */
	public function form( $instance ) {

		$title = ( isset( $instance[ 'widget_title' ] ) ) ? strip_tags( $instance[ 'widget_title' ] ) : '';
		$sort_order = ( isset( $instance[ 'widget_sort_order' ] ) ) ? strip_tags( $instance[ 'widget_sort_order' ] ) : '';
		$link_type = ( isset( $instance[ 'widget_title' ] ) ) ? esc_attr( $instance[ 'widget_link_type' ] ) : '';
		$show_current_blog = ( isset( $instance[ 'widget_show_current_blog' ] ) ) ? strip_tags( $instance[ 'widget_show_current_blog' ] ) : '';
		?>
		<p>
			<label for='<?php echo $this->get_field_id( 'mlp_widget_title' ); ?>'><?php _e( 'Title:', 'multilingualpress' ); ?></label><br />
			<input class="widefat" type ='text' id='<?php echo $this->get_field_id( "mlp_widget_title" ); ?>' name='<?php echo $this->get_field_name( 'mlp_widget_title' ); ?>' value='<?php echo $title; ?>'>
		</p>
		<p>
			<label for='<?php echo $this->get_field_id( 'mlp_widget_sort_order' ); ?>'><?php _e( 'Sort Order:', 'multilingualpress' ); ?></label><br />
			<select class="widefat" id='<?php echo $this->get_field_id( 'mlp_widget_sort_order' ); ?>' name='<?php echo $this->get_field_name( 'mlp_widget_sort_order' ); ?>' >
				<option <?php selected( $sort_order, 'name' ); ?> value="name"><?php _e( 'by Name', 'multilingualpress' ); ?></option>
				<option <?php selected( $sort_order, 'blogid' ); ?> value="blogid"><?php _e( 'by Blog ID', 'multilingualpress' ); ?></option>
			</select>
		</p>
		<p>
			<label for='<?php echo $this->get_field_id( 'mlp_widget_link_type' ); ?>'><?php _e( 'Link-Type:', 'multilingualpress' ); ?></label><br />
			<select class="widefat" id='<?php echo $this->get_field_id( 'mlp_widget_link_type' ); ?>' name='<?php echo $this->get_field_name( 'mlp_widget_link_type' ); ?>' >
				<option <?php selected( $link_type, 'text' ); ?> value="text"><?php _e( 'Text', 'multilingualpress' ); ?></option>
				<option <?php selected( $link_type, 'flag' ); ?> value="flag"><?php _e( 'Flag', 'multilingualpress' ); ?></option>
				<option <?php selected( $link_type, 'text_flag' ); ?> value="text_flag"><?php _e( 'Text &amp; Flag', 'multilingualpress' ); ?></option>
				<option <?php selected( $link_type, 'lang_code' ); ?> value="lang_code"><?php _e( 'Language code', 'multilingualpress' ); ?></option>
			</select>
		</p>
		<p>
			<label for='<?php echo $this->get_field_id( 'mlp_widget_show_current_blog' ); ?>'><?php _e( 'Show Current Blog:', 'multilingualpress' ); ?></label>
			<input <?php checked( $show_current_blog, '1' ); ?> type="checkbox" id="<?php echo $this->get_field_id( 'mlp_widget_show_current_blog' ); ?>" name="<?php echo $this->get_field_name( 'mlp_widget_show_current_blog' ); ?>" />
		</p>
		<?php
	}

	/**
	 * Callback for widget update
	 *
	 * @since	0.1
	 * @access	public
	 * @param	array $new_instance | new widget settings
	 * @param	array $instance | widget settings
	 * @uses	strip_tags, esc_attr
	 * @return	array $new_instance | new widget settings
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance[ 'widget_title' ] = strip_tags( $new_instance[ 'mlp_widget_title' ] );
		$instance[ 'widget_link_type' ] = esc_attr( $new_instance[ 'mlp_widget_link_type' ] );
		$instance[ 'widget_sort_order' ] = esc_attr( $new_instance[ 'mlp_widget_sort_order' ] );
		$instance[ 'widget_show_current_blog' ] = $new_instance[ 'mlp_widget_show_current_blog' ] == 'on' ? TRUE : FALSE;

		return $instance;
	}

	/**
	 * Frontend display
	 *
	 * @since	0.1
	 * @access	public
	 * @param	array $args
	 * @param	array $instance | widget settings
	 * @uses	mlp_show_linked_elements
	 * @return	void
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		if ( ! isset( $instance[ 'widget_sort_order' ] ) )
			$instance[ 'widget_sort_order' ] = 'blogid';

		$output = mlp_show_linked_elements(
			array(
				'link_text' => $instance[ 'widget_link_type' ],
				'sort' => $instance[ 'widget_sort_order' ],
				'show_current_blog' => $instance[ 'widget_show_current_blog' ] == '1' ? TRUE : FALSE,
				'echo' => FALSE
			)
		);

		if ( '' == $output )
			return;

		echo $before_widget;

		// Display Title (optional)
		if ( $instance[ 'widget_title' ] )
			echo $before_title . apply_filters( 'widget_title', $instance[ 'widget_title' ] ) . $after_title;

		echo $output . $after_widget;
	}

	/**
	 * Registers the widget
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	register_widget
	 * @return	void
	 */
	public static function widget_register() {
		register_widget( 'mlp_widget' );
	}

}

// Initialize widget
add_filter( 'widgets_init', array( 'Mlp_Widget', 'widget_register' ) );
?>