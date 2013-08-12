<?php
/**
 * Settings Page
 *
 * @author		fb, rw, ms, th
 * @package		mlp
 * @subpackage	settings
 *
 */
class Mlp_Settingspage extends Multilingual_Press {

	/**
	 * The static class object variable
	 *
	 * @static
	 * @since  0.1
	 * @var    string
	 */
	static public $settings_class_object = NULL;

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
	 * Tab holder
	 *
	 * the key defines the tabname and the prefix for a private function of this class
	 * to show the metabox. To do so, define a private function like {tablame}_jobs_tab()
	 * The order of items in the array definies the order of the tabs
	 *
	 * @static
	 * @since 0.5
	 * @var array
	 */
	static private $tabs = NULL;

	/**
	 * to load the object and get the current state
	 *
	 * @access	public
	 * @since	0.1
	 * @return	$settings_class_object
	 */
	public static function get_object() {

		if ( NULL == self::$settings_class_object )
			self::$settings_class_object = new self;
		return self::$settings_class_object;
	}

	/**
	 * init function to register all used hooks and set the Database Table
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	add_filter
	 * @return	void
	 */
	function __construct() {

		self::$tabs = array(
			'mlp-options'	=> __( 'Settings', 'multilingualpress' )
		);

		add_filter( 'network_admin_menu', array( $this, 'settings_page' ) );
		add_filter( 'admin_post_mlp_update_settings', array( $this, 'update_settings' ) );
		add_filter( 'admin_post_mlp_update_modules', array( $this, 'update_modules' ) );
		add_filter( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Load the scripts for the options page
	 *
	 * @param	string $hook | current page identifier
	 * @uses	wp_enqueue_script, wp_enqueue_style
	 * @since	0.1
	 * @return	void
	 */
	public function admin_scripts( $hook = NULL ) {

		if ( 'settings_page_mlp-pro-options' == $hook ) {
			wp_enqueue_script( 'dashboard' );
			wp_enqueue_style( 'dashboard' );
		}
	}

	/**
	 * Add Multilingual Press networks settings
	 * and module page
	 *
	 * @since	1.2
	 * @uses	add_submenu_page, add_filter
	 * @return	void
	 */
	function settings_page() {

		// Adding "Module Tab" if modules avilable
		if ( 0 < count( parent::$class_object->loaded_modules ) )
			self::$tabs[ 'mlp-modules' ] = __( 'Modules', 'multilingualpress' );

		// Register options page
		$this->options_page = add_submenu_page( 'settings.php', __( 'mlp Options', 'multilingualpress' ), __( 'Multilingual Press', 'multilingualpress' ), 'manage_network_options', 'mlp', array( $this, 'mlp_options' ) );

		// Callback for adding more metaboxes
		add_filter( 'load-'.$this->options_page, array( $this, 'metaboxes_options_page') );
	}

	/**
	 * This callback allows modules
	 * to hook in their own metaboxes;
	 *
	 * @since	0.1
	 * @uses	do_action
	 * @return	void
	 */
	public function metaboxes_options_page() {

		do_action( 'mlp_options_page_add_metabox' );
	}

	/**
	 * MLP Settings Tab Handler for better UI
	 *
	 * @since 0.5
	 * @return void
	 */
	function mlp_options() {

		// set the current tab to the first element, if no tab is in request
		if ( ISSET( $_REQUEST[ 'tab' ] ) && array_key_exists( $_REQUEST[ 'tab' ], self::$tabs ) ) {
			$current_tab = $_REQUEST[ 'tab' ];
			$current_tabname = self::$tabs[ $current_tab ];
		}
		else {
			$current_tab = current( array_keys( self::$tabs ) );
			$current_tabname = self::$tabs[ $current_tab ];
		}

		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2 class="nav-tab-wrapper">

				<?php _e( 'Multilingual Press', 'multilingualpress' ); ?>

				<?php
					foreach( self::$tabs as $tab_handle => $tabname ) {
						// set the url to the tab
						$url = network_admin_url( 'settings.php?page=mlp&tab=' . $tab_handle );
						// check, if this is the current tab
						$active = ( $current_tab == $tab_handle ) ? ' nav-tab-active' : '';
						printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $active, $tabname );
					}
					?>
				</h2>
				<br />

			<?php if ( ISSET( $_GET[ 'message' ] ) && 'updated' == $_GET[ 'message' ] ) { ?>
				<div class="updated">
					<p><?php _e( 'Settings saved', 'multilingualpress' ); ?></p>
				</div>
			<?php } ?>

			<div id="poststuff" class="metabox-holder has-right-sidebar">

				<div id="side-info-column" class="inner-sidebar">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<div id="wp-liveticker-inpsyde" class="postbox">
							<h3 class="hndle"><span><?php _e( 'Powered by', 'multilingualpress' ); ?></span></h3>
							<div class="inside">
								<p style="text-align: center;"><a href="http://inpsyde.com"><img src="<?php echo plugins_url( 'images/inpsyde_logo.jpg' , dirname( __FILE__ ) ) ?>" style="border: 7px solid #fff;" /></a></p>
								<p><?php _e( 'This plugin is powered by <a href="http://inpsyde.com">Inpsyde.com</a> - Your expert for WordPress, BuddyPress and bbPress.', 'multilingualpress' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<div id="post-body">
					<div id="post-body-content">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php
								switch ( $current_tab ) {
									case 'mlp-modules':
										$this->modules_form();
										break;
									case 'mlp-options':
									default:
										$this->settings_form();
										break;
								}
							?>

						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * The network settings page for
	 * Multilingual Press. Modules use hook
	 * 'mlp_settings_add_fields' to
 	 * add fields to the form.
	 *
	 * @since	1.2
	 * @uses	_e, admin_url, wp_nonce_field, do_meta_boxes, submit_button
	 * @return	void
	 *
	 * @TODO: check whether something was hooked here,
	 * otherwise display "no options available"
	 */
	public function settings_form() {
		?>
		<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_settings' ); ?>" method="post">
			<?php
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			?>

			<div id="poststuff" class="metabox-holder">
				<?php
				wp_nonce_field( 'mlp_settings' );
				do_meta_boxes( $this->options_page, 'normal', FALSE );
				submit_button();
				?>
			</div>
		</form>
		<?php
	}

	/**
	 * Validate and save user input. Modules
	 * can hook into this function via 'mlp_settings_save_fields'
	 *
	 * @since	0.1
	 * @uses	check_admin_referer, current_user_can, wp_die, do_action,
	 * 			wp_safe_redirect, network_admin_url
	 * @return	void
	 */
	public function update_settings() {

		check_admin_referer( 'mlp_settings' );

		if ( ! current_user_can( 'manage_network_options' ) )
			wp_die( 'FU' );

		// process your fields from $_POST here and update_site_option
		do_action( 'mlp_settings_save_fields', $_POST );

		wp_safe_redirect( network_admin_url( 'settings.php?page=mlp&tab?mlp-pro-options&message=updated' ) );
		exit;
	}

	/**
	 * Modules Manager
	 *
	 * @since	0.1
	 * @uses	get_site_option, _e, admin_url, wp_nonce_field,
	 * 			do_action, submit_button
	 * @return	void
	 */
	public function modules_form() {

		// Get modules data
		$states = get_site_option( 'state_modules' );
		$loaded_modules = parent::$class_object->loaded_modules;

		// Draw the form
		?>
		<form action="<?php echo admin_url( 'admin-post.php?action=mlp_update_modules' ); ?>" method="post">
			<?php wp_nonce_field( 'mlp_modules' ); ?>

			<div id="mlp_help" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Multilingual Press - Modules', 'multilingualpress' ); ?></h3>
				<div class="inside">
					<p><?php _e( 'In the below boxes, there are all modules which come with MultilingualPress Pro. If you don\'t need a module just deactivate the checkbox and save the settings. If you need help for a module there is a detailed description in every module.', 'multilingualpress' ); ?></p>
				</div>
			</div>

				<?php foreach ( $loaded_modules AS $module => $reg ) :
					if ( TRUE === apply_filters( 'mlp_dont_show_module_' . $module, FALSE ) )
						continue;
				?>

				<div id="mlp_help" class="postbox">
					<h3 class="hndle"><span><?php echo $states[ $module ][ 'display_name' ]; ?></h3>
					<div class="inside">
						<p><?php
						// strip "class-" from slug
						$classname = substr( $states[ $module ][ 'slug' ], 6 );
						if ( method_exists( $classname, 'get_module_description' ) ) {
							// wrapper for php < 5.3
							$classobj = new $classname;
							echo $classobj->get_module_description();
						}
						?></p>
						<p>
							<label for="mlp_state_<?php echo $module; ?>">
							<input type="checkbox" <?php
								echo ( array_key_exists( $module, $states ) && 'on' == $states[ $module ][ 'state' ] ) ? 'checked="checked"' : '';
								?> id="mlp_state_<?php
								echo $module;
								?>" value="true" name="mlp_state_<?php
								echo $module;
								?>" />
							<?php _e( 'Activate this module', 'multilingualpress' ); ?>
							</label>
						</p>
					</div>
				</div>
				<?php endforeach; ?>
			<?php
				do_action( 'mlp_modules_add_fields' );
				submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Module Manager save current
	 * module states
	 *
	 * @since	0.1
	 * @uses	check_admin_referer, current_user_can, get_site_option,
	 * 			update_site_option, do_action, wp_redirect, network_admin_url
	 * @return	void
	 */
	public function update_modules() {

		check_admin_referer( 'mlp_modules' );

		$modules = array();

		if ( ! current_user_can( 'manage_network_options' ) )
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
			foreach ( $new_states AS $module => $meta ) {

				// Don't bother if already turned off
				if ( 'off' == $meta[ 'state' ] )
					continue;

				// Register as off
				$current_states[ $module ][ 'state' ] = 'off';

				// Fire deactivation callback of module, if given
				if ( ISSET( $meta[ 'deactivation' ] ) && is_array( $meta[ 'deactivation' ] ) ) {
					@list( $class, $method ) = $meta[ 'deactivation' ];
					if ( method_exists( $class, $method ) )
						call_user_func( $class . '::' . $method );
				}
			}
		}



		// Activate modules
		foreach ( $modules AS $module => $state ) {
			// Save state
			$current_states[ $module ][ 'state' ] = 'on';
		}

		// Update module states
		update_site_option( 'state_modules', $current_states );

		// process your fields from $_POST here and update_site_option
		do_action( 'mlp_modules_save_fields', $_POST );

		wp_safe_redirect( network_admin_url( 'settings.php?page=mlp&tab=mlp-modules&message=updated' ) );
		exit;
	}

}