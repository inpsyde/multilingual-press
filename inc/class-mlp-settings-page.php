<?php
if ( ! class_exists( 'inpsyde_multilingualpress_settingspage' ) ) {

	class inpsyde_multilingualpress_settingspage extends inpsyde_multilingualpress {

		static protected $class_object = NULL; // static class object variable
		static private $mlp = FALSE; // Localization-var
		protected $registered_modules = FALSE; // registered modules

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
			$this->mlp = inpsyde_multilingualpress::get_textdomain();
			
			add_action( 'network_admin_menu', array( $this, 'settings_page' ) );
			add_action( 'admin_post_mlp_update_settings', array( $this, 'update_settings' ) );
			add_action( 'admin_post_mlp_update_modules', array( $this, 'update_modules' ) );
		}

		/**
		 * Add Multilingual Press networks settings
		 * and module page
		 * 
		 * @since 1.2
		 */
		function settings_page() {
			
			// Get the loaded modules from parent class
			$this->registered_modules = inpsyde_multilingualpress::$class_object->registered_modules;
			
			// No modules available? The forget about the settings page and module manager.
			if ( ! $this->registered_modules ) return;
			
			add_submenu_page(
				'settings.php', __( 'mlp Options', $this->mlp ), __( 'MlP Options', $this->mlp ), 'manage_network_options', 'mlp-pro-options', array( $this, 'settings_form' )
			);
			add_submenu_page(
				'settings.php', __( 'mlp Modules', $this->mlp ), __( 'MlP Modules', $this->mlp ), 'manage_network_options', 'mlp-pro-modules', array( $this, 'modules_form' )
			);
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
		function settings_form() {
			?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br></div>
				<h2><?php _e( 'Multilingual Press Options', $this->mlp ); ?></h2>
				<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_settings' ); ?>" method="post">
					
					<?php wp_nonce_field( 'mlp_settings' ); ?>

					<?php do_action( 'mlp_settings_add_fields' );
					
					$has_options = did_action( 'mlp_settings_add_fields' );
							
					submit_button(); ?>
					
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
		function update_settings() {

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
			//p( $states, "STATE" );
			$registered_modules = inpsyde_multilingualpress::$class_object->registered_modules;
			//p( $registered_modules, "MODULES" );
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
							foreach ( $registered_modules AS $module => $reg ) {
								?>
								<tr>
									<th scope="row">
										<?php echo $module; ?>
									</th>
									<td>
										<input type="checkbox" <?php echo ( array_key_exists( $module, $states ) AND 'on' == $states[ $module ] ) ? 'checked="checked"' : ''; ?> id="mlp_state_<?php echo $module; ?>" value="true" name="mlp_state_<?php echo $module; ?>" />
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

			$states = get_site_option( 'state_modules' );
			$registered_modules = inpsyde_multilingualpress::$class_object->registered_modules;

			// Walk user input
			foreach ( $_POST AS $module => $state ) {
				if ( 0 === strpos( $module, 'mlp_state_' ) )
					$modules[ str_replace( 'mlp_state_', '', $module ) ] = $state;
			}
			
			// Deactivate previously activated modules
			$new_states = array_diff_key( $states, $modules );
			if ( is_array( $new_states ) ) {
				foreach ( $new_states AS $module => $state ) {
					$current_states[ $module ] = 'off';
				}
			}

			// Activate modules
			foreach ( $modules AS $module => $state ) {
				$current_states[ $module ] = 'on';
			}

			// Update module states
			update_site_option( 'state_modules', $current_states );

			// process your fields from $_POST here and update_site_option
			do_action( 'mlp_modules_save_fields', $_POST );

			wp_redirect( admin_url( 'network/settings.php?page=mlp-pro-modules' ) );
			exit;
		}

	}
}
?>