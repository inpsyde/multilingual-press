<?php
/**
 * Multilingual Press widget class
 * Version: 0.7a
 * 
 */

/**
 * Changelog
 * 
 * 0.7a
 * - Use URL-parameter to avoid redirection 
 */
if ( ! class_exists( 'Mlp_Widget' ) ) {

	class Mlp_Widget extends WP_Widget {

		private $textdomain = '';

		/**
		 * Widget init
		 * 
		 */
		public function mlp_widget() {
			
			$this->textdomain = inpsyde_multilingualpress :: get_textdomain();
			
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
		 * @return void
		 */
		public function widget( $args, $instance ) {
			
			extract( $args );
			
			$languages = mlp_get_available_languages( TRUE );
			$language_titles = mlp_get_available_languages_titles();
			
			if ( ! ( 0 < count( $languages ) ) )
				return;
			
			echo $before_widget;

			// Display Title (optional)
			if ( $instance[ 'widget_title' ] )
				echo $before_title . apply_filters( 'widget_title', $instance[ 'widget_title' ] ) . $after_title;

			if ( is_single() || is_page() )
				$linked_elements = mlp_get_linked_elements( get_the_id() );

			echo '<ul>';

			foreach ( $languages as $language_blog => $language_string ) {
				
				// Get params
				$flag = mlp_get_language_flag( $language_blog );
				$title = mlp_get_available_languages_titles( TRUE );
				
				// Display type
				if ( 'flag' == $instance[ 'widget_link_type' ] && '' != $flag ) {
					
					$display = '<img src="' . $flag . '" alt="' . $languages[ $language_blog ] . '" title="' . $title[ $language_blog ] . '" />';
				}
				else if ( 'text' == $instance[ 'widget_link_type' ] && ! empty( $language_titles[ $language_blog ] ) )
					$display = $language_titles[ $language_blog ];
				else 
					$display = $languages[ $language_blog ];
				
				$class = ( get_current_blog_id() == $language_blog ) ? 'id="mlp_current_locale"' : '';

				// Check post status
				$post = ( ISSET( $linked_elements[ $language_blog ] ) ) ? get_blog_post( $language_blog, $linked_elements[ $language_blog ] ) : '';
				
				// Output link elements
				echo '<li><a ' . $class . ' href="' . ( ( is_single() || is_page() ) && ISSET( $linked_elements[ $language_blog ] ) && 'publish' === $post->post_status ? get_blog_permalink( $language_blog, $linked_elements[ $language_blog ] ) : get_site_url( $language_blog ) ) . '?noredirect=' . $language_string . '">' . $display . '</a></li>';
			}

			echo '</ul>';

			echo $after_widget;
		}
		
		public function widget_register() {
			register_widget( 'mlp_widget' );	
		}
		
	} // Class END;

	// Initialize widget
	add_action( 'widgets_init', array( 'Mlp_Widget', 'widget_register' ) );
}
?>