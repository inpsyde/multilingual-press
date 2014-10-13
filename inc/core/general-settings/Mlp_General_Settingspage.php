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
	 * @type Mlp_Assets_Interface
	 */
	private $assets;

	/**
	 * Constructor
	 *
	 * @param   Mlp_Module_Manager_Interface $modules
	 * @param   Mlp_Assets_Interface         $assets
	 */
	public function __construct( Mlp_Module_Manager_Interface $modules, Mlp_Assets_Interface $assets ) {
		$this->modules = $modules;
		$this->assets = $assets;
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

		$this->assets->provide( array ( 'mlp_backend_js', 'mlp_backend_css' ) );
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