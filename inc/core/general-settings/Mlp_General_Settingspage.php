<?php

/**
 * Settings page controller.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_General_Settingspage {

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $modules;

	/**
	 * @var string
	 */
	private $page_hook;

	/**
	 * Constructor
	 *
	 * @param   Mlp_Module_Manager_Interface $modules
	 */
	public function __construct( Mlp_Module_Manager_Interface $modules ) {
		$this->modules = $modules;
	}

	/**
	 * Set up the page.
	 *
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function setup() {

		$this->model   = new Mlp_General_Settings_Module_Mapper( $this->modules );
		$this->view    = new Mlp_General_Settings_View( $this->model );

		add_filter( 'network_admin_menu', array( $this, 'register_settings_page' ) );
		add_filter( 'admin_post_mlp_update_modules', array( $this->model, 'update_modules' ) );
		add_action( 'network_admin_notices', array ( $this, 'show_update_message' ) );
	}

	/**
	 * Add MultilingualPress networks settings
	 * and module page
	 *
	 * @return	void
	 */
	public function register_settings_page() {

		// Register options page
		$this->page_hook = add_submenu_page(
			'settings.php',
			__( 'MultilingualPress', 'multilingualpress' ),
			__( 'MultilingualPress', 'multilingualpress' ),
			'manage_network_options',
			'mlp',
			array( $this->view, 'render_page' )
		);

		add_filter( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Load the scripts for the options page
	 *
	 * @param	string $hook | current page identifier
	 * @return	void
	 */
	public function admin_scripts( $hook = NULL ) {

		if ( $this->page_hook === $hook ) {
			wp_enqueue_script( 'dashboard' );
			wp_enqueue_style( 'dashboard' );
			wp_enqueue_style( 'mlp-admin-css' );
		}
	}

	/**
	 * Handle update messages.
	 *
	 * @return void
	 */
	public function show_update_message() {

		global $hook_suffix;

		if ( empty ( $hook_suffix ) or $this->page_hook !== $hook_suffix )
			return;

		if ( ! isset ( $_GET[ 'message' ] ) )
			return;

		$msg    = __( 'Settings saved.', 'multilingualpress' );
		$notice = new Mlp_Admin_Notice( $msg, array ( 'class' => 'updated' ) );
		$notice->show();
	}
}