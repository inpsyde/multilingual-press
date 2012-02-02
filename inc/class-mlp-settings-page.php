<?php
/**
 * MultilingualPress
 * Class name: Inpsyde_Multilingualpress_Settingspage
 * The plugins' settings page and module manager. Modules 
 * can hook into this (not fully implemented yet)
 * 
 * @version 0.2
 * 
 */
if ( ! class_exists( 'Inpsyde_Multilingualpress_Settingspage' ) ) {

	class Inpsyde_Multilingualpress_Settingspage extends Inpsyde_Multilingualpress {

		/**
		 * The static class object variable
		 *
		 * @static
		 * @since  0.1
		 * @var    string
		 */
		static public $class_object = NULL;
		
		/**
		 * The var containing the plugins' textdomain
		 *
		 * @static
		 * @since  0.1
		 * @var    string
		 */				
		static private $mlp = FALSE; 
		
		/**
		 * Registered modules
		 *
		 * @static
		 * @since  0.1
		 * @var    string
		 */
		protected $loaded_modules = FALSE; 

		/**
		 * Handler for the custom network options page
		 *
		 * @static
		 * @since  0.2
		 * @var    string
		 */
		public $options_page = FALSE; 
				
		/**
		 * Handler for the network module manager
		 *
		 * @static
		 * @since  0.2
		 * @var    string
		 */
		public $modules_page = FALSE;				 
				
		/**
		 * to load the object and get the current state 
		 *
		 * @access public
		 * @since 0.1
		 * @return $class_object
		 */
		function get_object() {

			if ( NULL == self::$class_object ) {
				self::$class_object = new self;
			}
			return self::$class_object;
		}

		/**
		 * init function to register all used hooks and set the Database Table 
		 *
		 * @access public
		 * @since 0.1
		 * @uses add_action, get_site_option
		 * @return void
		 */
		function __construct() {
									 
			// Set some class vars
			$this->mlp = parent::get_textdomain();
			
			add_action( 'network_admin_menu', array( $this, 'settings_page' ) );
			add_action( 'admin_post_mlp_update_settings', array( $this, 'update_settings' ) );
			add_action( 'admin_post_mlp_update_modules', array( $this, 'update_modules' ) );
						
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
		
		/**
		 * Load the scripts for the options page
		 * 
		 * @param string $hook | current page identifier 
		 */
		public function admin_scripts( $hook ) {
			
			if ( 'settings_page_mlp-pro-options' == $hook ) {
				wp_enqueue_script( 'dashboard' );
				wp_enqueue_style( 'dashboard' );
			}
		}

		/**
		 * Add Multilingual Press networks settings
		 * and module page
		 * 
		 * @since 1.2
		 */
		function settings_page() {
			
			// Get the loaded modules from parent class
			$this->loaded_modules = parent::$class_object->loaded_modules;
			
			// No modules available? Then forget about the settings page and module manager.
			if ( ! $this->loaded_modules ) return;
			
			$this->options_page = add_submenu_page(
				'settings.php', 
				__( 'mlp Options', $this->mlp ), 
				__( 'MlP Options', $this->mlp ), 
				'manage_network_options', 
				'mlp-pro-options', 
				array( $this, 'settings_form' )
			);
			$this->modules_page = add_submenu_page(
				'settings.php', 
				__( 'mlp Modules', $this->mlp ), 
				__( 'MlP Modules', $this->mlp ), 
				'manage_network_options', 
				'mlp-pro-modules', 
				array( $this, 'modules_form' )
			);
			
			add_action( 'load-'.$this->options_page, array( $this, 'metaboxes_options_page') );
		}
		
		/**
		 * Set an Action Hook for add meta boxes
		 * 
		 * @since   0.1
		 * @return  void
		 */
		public function metaboxes_options_page() {
		
			do_action( 'mlp_options_page_add_metabox' );
		}
		
		/**
		 * The network settings page for
		 * Multilingual Press. Modules use hook
		 * 'mlp_settings_add_fields' to
		 * add fields to the form.
		 * 
		 * @since 1.2
		 * 
		 * @TODO: check whether something was hooked here,
		 * otherwise display "no options available"
		 * 
		 */
		public function settings_form() {
			
			?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br></div>
				<h2><?php _e( 'Multilingual Press Options', $this->mlp ); ?></h2>
								<br />
				<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_settings' ); ?>" method="post">
									<?php
									wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
									wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
									?>
									
					
									<div id="poststuff" class="metabox-holder">
					<?php wp_nonce_field( 'mlp_settings' ); ?>

					<?php do_meta_boxes( $this->options_page, 'normal', FALSE );
										//do_action( 'mlp_settings_add_fields' );
					
					//$has_options = did_action( 'mlp_settings_add_fields' );
							
					submit_button(); ?>
									</div>
					
				</form>
			</div>
			<?php
		}
		
		/**
		 * Validate and save user input. Modules
		 * can hook into this function via 'mlp_settings_save_fields'
		 * 
		 * @since 1.2
		 * 
		 */
		public function update_settings() {

			check_admin_referer( 'mlp_settings' );

			if ( ! current_user_can( 'manage_network_options' ) )
				wp_die( 'FU' );

			// process your fields from $_POST here and update_site_option
			do_action( 'mlp_settings_save_fields', $_POST );

			wp_redirect( admin_url( 'network/settings.php?page=mlp-pro-options' ) );
			exit;
		}

		/**
		 * Modules Manager
		 * 
		 * @since 1.3
		 */
		public function modules_form() {

			$states = get_site_option( 'state_modules' );
			$loaded_modules = parent::$class_object->loaded_modules;
			?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br></div>
				<h2><?php _e( 'Multilingual Press Module Manager', $this->mlp ); ?></h2>
				<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_modules' ); ?>" method="post">
					<?php wp_nonce_field( 'mlp_modules' ); ?>
					<h3><?php _e( 'List of installed modules', $this->mlp ); ?></h3>
					<table class="form-table">
						<tbody>
							<?php
							foreach ( $loaded_modules AS $module => $reg ) {
								?>
								<tr>
									<th scope="row">
										<?php echo $states[ $module ][ 'display_name' ]; ?>
									</th>
									<td>
										<input type="checkbox" <?php echo ( array_key_exists( $module, $states ) && 'on' == $states[ $module ][ 'state' ] ) ? 'checked="checked"' : ''; ?> id="mlp_state_<?php echo $module; ?>" value="true" name="mlp_state_<?php echo $module; ?>" />
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>

					<?php do_action( 'mlp_modules_add_fields' ); ?>

					<?php submit_button(); ?>
					
				</form>
			</div>
			<?php
		}

		/**
		 * Module Manager save current
		 * module states
		 * 
		 * @since 1.3
		 * 
		 */
		public function update_modules() {

			check_admin_referer( 'mlp_modules' );
			
			$modules = array();

			if ( !current_user_can( 'manage_network_options' ) )
				wp_die( 'FU' );

			$current_states = get_site_option( 'state_modules' );
			$loaded_modules = parent::$class_object->loaded_modules;
						
			// Walk user input
			foreach ( $_POST AS $module => $state ) {
				if ( 0 === strpos( $module, 'mlp_state_' ) )
					$modules[ str_replace( 'mlp_state_', '', $module ) ] = $state;
			}
									
			// Deactivate previously activated modules
			$new_states = array_diff_key( $current_states, $modules );
						
			if ( is_array( $new_states ) ) {
				foreach ( $new_states AS $module => $state ) {
					$current_states[ $module ][ 'state' ] = 'off';
				}
			}
										 
						

			// Activate modules
			foreach ( $modules AS $module => $state ) {
				$current_states[ $module ][ 'state' ] = 'on';
			}
						
			// Update module states
			update_site_option( 'state_modules', $current_states );

			// process your fields from $_POST here and update_site_option
			do_action( 'mlp_modules_save_fields', $_POST );

			wp_redirect( admin_url( 'network/settings.php?page=mlp-pro-modules' ) );
			exit;
		}

	} // end class
	
} // end if class exists
?>