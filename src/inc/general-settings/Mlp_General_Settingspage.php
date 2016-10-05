<?php

use Inpsyde\MultilingualPress\Asset\AssetManager;
use Inpsyde\MultilingualPress\Common\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Module\ModuleManager;

/**
 * Settings page controller.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_General_Settingspage {

	/**
	 * @type AssetManager
	 */
	private $asset_manager;

	/**
	 * @var Mlp_General_Settings_Module_Mapper
	 */
	private $model;

	/**
	 * @var ModuleManager
	 */
	private $modules;

	/**
	 * @var string
	 */
	private $page_hook;

	/**
	 * Constructor
	 *
	 * @param ModuleManager $modules
	 * @param AssetManager  $asset_manager
	 */
	public function __construct( ModuleManager $modules, AssetManager $asset_manager ) {

		$this->modules = $modules;

		$this->asset_manager = $asset_manager;
	}

	/**
	 * Set up the page.
	 *
	 * @wp-hook plugins_loaded
	 *
	 * @return void
	 */
	public function setup() {

		$this->model = new Mlp_General_Settings_Module_Mapper( $this->modules );

		add_filter( 'network_admin_menu', [ $this, 'register_settings_page' ] );
		add_filter( 'admin_post_mlp_update_modules', [ $this->model, 'update_modules' ] );
		add_action( 'network_admin_notices', [ $this, 'show_update_message' ] );
	}

	/**
	 * Add MultilingualPress network settings and module page.
	 *
	 * @return void
	 */
	public function register_settings_page() {

		$view = new Mlp_General_Settings_View( $this->model );

		// Register options page
		$this->page_hook = add_submenu_page(
			'settings.php',
			__( 'MultilingualPress', 'multilingual-press' ),
			__( 'MultilingualPress', 'multilingual-press' ),
			'manage_network_options',
			'mlp',
			[ $view, 'render_page' ]
		);

		$this->asset_manager->enqueue_script( 'multilingualpress-admin' );
		$this->asset_manager->enqueue_style( 'multilingualpress-admin' );
	}

	/**
	 * Handle update messages.
	 *
	 * @return void
	 */
	public function show_update_message() {

		global $hook_suffix;

		if ( empty( $hook_suffix ) || $this->page_hook !== $hook_suffix ) {
			return;
		}

		if ( ! isset( $_GET[ 'message' ] ) ) {
			return;
		}

		( new AdminNotice( '<p>' . __( 'Settings saved.', 'multilingual-press' ) . '</p>' ) )->render();
	}
}
