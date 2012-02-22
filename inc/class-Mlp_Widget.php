<?php
/**
 * Widget
 *
 * @author		fb, rw, ms, th
 * @package		mlp
 * @subpackage	widget
 *
 */
class Mlp_Widget extends WP_Widget {

	/**
	 * The textdomain
	 * 
	 * @var		string | The textdomain
	 * @since	0.1
	 */
	private $textdomain = '';

	/**
	 * Widget init
	 */
	public function mlp_widget() {
		
		$mlp = Multilingual_Press::get_object();
		$this->textdomain = $mlp->get_textdomain();
		
		$widget_ops = array(
			'classname' => 'Mlp_Widget',
			'description' => __( 'Multilingual Press Widget', $this->textdomain )
		);
		
		$this->WP_Widget( 'Mlp_Widget', __( 'Multilingual Press Widget', $this->textdomain ), $widget_ops );
	}

	/**
	 * Display widget admin form
	 * 
	 * @param array $instance | widget settings 
	 */
	public function form( $instance ) {

		$title = ( ISSET( $instance[ 'widget_title' ] ) ) ? strip_tags( $instance[ 'widget_title' ] ) : '';
		$link_type = ( ISSET( $instance[ 'widget_title' ] ) ) ? esc_attr( $instance[ 'widget_link_type' ] ) : '';
		?>
		<p>
			<label for='<?php echo $this->get_field_id( "mlp_widget_title" ); ?>'><?php _e( 'Title:', $this->textdomain ); ?></label><br /> 
			<input class="widefat" type ='text' id='<?php echo $this->get_field_id( "mlp_widget_title" ); ?>' name='<?php echo $this->get_field_name( "mlp_widget_title" ); ?>' value='<?php echo $title; ?>'>
		</p>
		<p>
			<label for='<?php echo $this->get_field_id( "mlp_widget_link_type" ); ?>'><?php _e( 'Link-Type:', $this->textdomain ); ?></label><br />	
			<select class="widefat" id='<?php echo $this->get_field_id( "mlp_widget_link_type" ); ?>' name='<?php echo $this->get_field_name( "mlp_widget_link_type" ); ?>' >
				<option <?php selected( $link_type, 'text' ); ?> value="text"><?php _e( 'Text', $this->textdomain ); ?></option>
				<option <?php selected( $link_type, 'flag' ); ?> value="flag"><?php _e( 'Flag', $this->textdomain ); ?></option>
				<option <?php selected( $link_type, 'text_flag' ); ?> value="text_flag"><?php _e( 'Text &amp; Flag', $this->textdomain ); ?></option>
				<option <?php selected( $link_type, 'lang_code' ); ?> value="lang_code"><?php _e( 'Language code', $this->textdomain ); ?></option>
			</select>
		</p>
		<?php
	}

	/**
	 * Callback for widget update
	 * 
	 * @param array $instance | widget settings 
	 * @param array $new_instance | new widget settings 
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance[ 'widget_title' ] = strip_tags( $new_instance[ 'mlp_widget_title' ] );
		$instance[ 'widget_link_type' ] = esc_attr( $new_instance[ 'mlp_widget_link_type' ] );

		return $instance;
	}

	/**
	 * Frontend display
	 * 
	 * @param array $args
	 * @param array $instance | widget settings
	 * @uses mlp_show_linked_elements
	 * @return void
	 */
	public function widget( $args, $instance ) {
		
		extract( $args );
		
		$output = mlp_show_linked_elements( $instance[ 'widget_link_type' ], FALSE );
		
		if ( '' == $output )
			return;
		
		echo $before_widget;

		// Display Title (optional)
		if ( $instance[ 'widget_title' ] )
			echo $before_title . apply_filters( 'widget_title', $instance[ 'widget_title' ] ) . $after_title;

		echo $output . $after_widget;
	}
	
	public function widget_register() {
		register_widget( 'mlp_widget' );	
	}
	
} // Class END;

// Initialize widget
add_action( 'widgets_init', array( 'Mlp_Widget', 'widget_register' ) );
?>