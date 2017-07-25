<?php # -*- coding: utf-8 -*-
/**
 * Class Multilingual_Press
 *
 * Kind of a front controller.
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Multilingual_Press {

	/**
	 * The linked elements table
	 *
	 * @since  0.1
	 * @var    string
	 */
	private $link_table = '';

	/**
	 * Local path to plugin file.
	 *
	 * @var string
	 */
	private $plugin_file_path;

	/**
	 * Overloaded instance for plugin data.
	 *
	 * @needs-refactoring
	 * @var Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor
	 *
	 * @param Inpsyde_Property_List_Interface $data
	 * @param wpdb $wpdb
	 */
	public function __construct( Inpsyde_Property_List_Interface $data, wpdb $wpdb = NULL ) {

		/* Someone has an old Free version active and activates the new Pro on
		 * top of that. The old Free version tries now to create an instance of
		 * this new version of the class, and the second parameter is missing.
		 * This is where we stop.
		 */
		if ( NULL === $wpdb )
			return;

		$this->plugin_data = $data;
		$this->wpdb        = $wpdb;
	}

	/**
	 * Initial setup handler.
	 *
	 * @global	$wpdb wpdb WordPress Database Wrapper
	 * @global	$pagenow string Current Page Wrapper
	 * @return void
	 */
	public function setup() {

		$this->prepare_plugin_data();
		$this->load_assets();
		$this->prepare_helpers();
		$this->plugin_data->freeze(); // no changes allowed anymore

		require 'functions.php';

		if ( ! $this->is_active_site() )
			return;

		// Hooks and filters
		add_action( 'inpsyde_mlp_loaded', array ( $this, 'load_plugin_textdomain' ), 1 );

		// Load modules
		$this->load_features();

		/**
		 * Runs before internal actions are registered.
		 *
		 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data object.
		 * @param wpdb                            $wpdb        Database object.
		 */
		do_action( 'inpsyde_mlp_init', $this->plugin_data, $this->wpdb );

		// Cleanup upon blog delete
		add_filter( 'delete_blog', array ( $this, 'delete_blog' ), 10, 2 );

		// Check for errors
		add_filter( 'all_admin_notices', array ( $this, 'check_for_user_errors_admin_notice' ) );

		add_action( 'wp_loaded', array ( $this, 'late_load' ), 0 );

		/**
		 * Runs after internal actions have been registered.
		 *
		 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data object.
		 * @param wpdb                            $wpdb        Database object.
		 */
		do_action( 'inpsyde_mlp_loaded', $this->plugin_data, $this->wpdb );

		if ( is_admin() )
			$this->run_admin_actions();
		else
			$this->run_frontend_actions();
	}

	/**
	 * Check if the current context needs more MultilingualPress actions.
	 *
	 * @return bool
	 */
	private function is_active_site() {

		global $pagenow;

		if ( in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
			return true;
		}

		if ( is_network_admin() )
			return TRUE;

		$relations = get_site_option( 'inpsyde_multilingual', array () );

		if ( array_key_exists( get_current_blog_id(), $relations ) )
			return TRUE;

		return FALSE;
	}
	/**
	 * @return void
	 */
	public function late_load() {

		/**
		 * Late loading event for MultilingualPress.
		 *
		 * @param Inpsyde_Property_List_Interface $plugin_data Plugin data object.
		 * @param wpdb                            $wpdb        Database object.
		 */
		do_action( 'mlp_and_wp_loaded', $this->plugin_data, $this->wpdb );
	}

	/**
	 * Load the localization
	 *
	 * @since 0.1
	 * @uses load_plugin_textdomain, plugin_basename
	 * @return void
	 */
	public function load_plugin_textdomain() {

		$rel_path = dirname( plugin_basename( $this->plugin_file_path ) )
				. $this->plugin_data->get( 'text_domain_path' );

		load_plugin_textdomain( 'multilingual-press', FALSE, $rel_path );
	}

	/**
	 * Register assets internally.
	 *
	 * @return void
	 */
	public function load_assets() {

		/** @type Mlp_Assets $assets */
		$assets = $this->plugin_data->get( 'assets' );

		$admin_url = admin_url();
		$admin_url = parse_url( $admin_url, PHP_URL_PATH );
		$admin_url = esc_url( $admin_url );

		$assets->add( 'mlp-admin', 'admin.js', array( 'backbone' ), array(
			'mlpSettings' => array(
				'urlRoot' => $admin_url,
			),
		) );

		$assets->add( 'mlp_admin_css', 'admin.css' );

		$assets->add( 'mlp-frontend', 'frontend.js' );

		$assets->add( 'mlp_frontend_css', 'frontend.css' );

		add_action( 'init', array( $assets, 'register' ), 0 );
	}

	/**
	 * Create network settings page.
	 *
	 * @return void
	 */
	private function load_module_settings_page() {

		$settings = new Mlp_General_Settingspage(
			$this->plugin_data->get( 'module_manager' ),
			$this->plugin_data->get( 'assets' )
		);
		add_action( 'plugins_loaded', array( $settings, 'setup' ), 8 );

		$plugin_file = defined( 'MLP_PLUGIN_FILE' )
			? plugin_basename( MLP_PLUGIN_FILE )
			: $this->plugin_data->get( 'plugin_base_name' );

		$url = network_admin_url( 'settings.php?page=mlp' );

		$action_link = new Mlp_Network_Plugin_Action_Link( array(
			'settings' => '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'multilingual-press' ) . '</a>',
		) );
		add_filter( "network_admin_plugin_action_links_$plugin_file", array( $action_link, 'add' ) );
	}

	/**
	 * Create site settings page.
	 *
	 * @return void
	 */
	private function load_site_settings_page() {

		$settings = new Mlp_General_Settingspage(
			$this->plugin_data->get( 'site_manager' ),
			$this->plugin_data->get( 'assets' )
		);
		$settings->setup();
		add_action( 'plugins_loaded', array( $settings, 'setup' ), 8 );
	}

	/**
	 * Find and load core and pro features.
	 *
	 * @access	public
	 * @since	0.1
	 * @return	array Files to include
	 */
	protected function load_features() {

		$found = array ();

		$path = $this->plugin_data->get( 'plugin_dir_path' ) . "/inc";

		if ( ! is_readable( $path ) )
			return $found;

		$files = glob( "$path/feature.*.php" );

		if ( empty ( $files ) )
			return $found;

		foreach ( $files as $file ) {
			$found[] = $file;
			require $file;
		}

		// We need the return value for tests.
		return $found;
	}

	/**
	 * Remove deleted blog from 'inpsyde_multilingual' site option and clean up linked elements table.
	 *
	 * @wp-hook delete_blog
	 *
	 * @param int $blog_id ID of the deleted blog.
	 *
	 * @return void
	 */
	public function delete_blog( $blog_id ) {

		global $wpdb;

		// Delete relations
		$site_relations = $this->plugin_data->get( 'site_relations' );
		$site_relations->delete_relation( $blog_id );

		// Update site option
		$blogs = (array) get_site_option( 'inpsyde_multilingual', array() );
		if ( isset( $blogs[ $blog_id ] ) ) {
			unset( $blogs[ $blog_id ] );
			update_site_option( 'inpsyde_multilingual', $blogs );
		}

		// Clean up linked elements table
		$sql = "
			DELETE
			FROM {$this->link_table}
			WHERE ml_source_blogid = %d
				OR ml_blogid = %d";
		$sql = $wpdb->prepare( $sql, $blog_id, $blog_id );
		$wpdb->query( $sql );
	}

	/**
	 * Use the current blog's language for the html tag.
	 *
	 * @wp-hook language_attributes
	 *
	 * @param string $output Language attributes HTML.
	 *
	 * @return string
	 */
	public function language_attributes( $output ) {

		$site_language = Mlp_Helpers::get_current_blog_language();
		if ( ! $site_language ) {
			return $output;
		}

		$language = get_bloginfo( 'language' );

		$site_language = str_replace( '_', '-', $site_language );

		return str_replace( $language, $site_language, $output );
	}

	/**
	 * Checks for errors
	 *
	 * @access	public
	 * @since	0.8
	 * @uses
	 * @return	boolean
	 */
	public function check_for_user_errors() {

		return $this->check_for_errors();
	}

	/**
	 * Checks for errors
	 *
	 * @access	public
	 * @since	0.9
	 * @uses
	 * @return	void
	 */
	public function check_for_user_errors_admin_notice() {

		if ( TRUE == $this->check_for_errors() ) {
			?><div class="error"><p><?php _e( 'You didn\'t setup any site relationships. You have to setup these first to use MultilingualPress. Please go to Network Admin &raquo; Sites &raquo; and choose a site to edit. Then go to the tab MultilingualPress and set up the relationships.' , 'multilingual-press' ); ?></p></div><?php
		}
	}

	/**
	 * Checks for errors
	 *
	 * @return	boolean
	 */
	public function check_for_errors() {

		if ( defined( 'DOING_AJAX' ) )
			return FALSE;

		if ( is_network_admin() )
			return FALSE;

		// Get blogs related to the current blog
		$all_blogs = get_site_option( 'inpsyde_multilingual', array () );

		if ( 1 > count( $all_blogs ) && is_super_admin() )
			return TRUE;

		return FALSE;
	}

	/**
	 * @return void
	 */
	private function run_admin_actions() {

		$module_manager = $this->plugin_data->get( 'module_manager' );
		if ( $module_manager->has_modules() ) {
			$this->load_module_settings_page();
		}

		$site_manager = $this->plugin_data->get( 'site_manager' );
		if ( $site_manager->has_modules() ) {
			$this->load_site_settings_page();
		}

		new Mlp_Network_Site_Settings_Controller( $this->plugin_data );

		new Mlp_Network_New_Site_Controller(
			$this->plugin_data->get( 'language_api' ),
			$this->plugin_data->get( 'site_relations' ),
			$this->plugin_data->get( 'assets' )
		);
	}

	/**
	 * @return void
	 */
	private function run_frontend_actions() {

		// Use correct language for html element
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );

		// frontend-hooks
		$hreflang = new Mlp_Hreflang_Header_Output( $this->plugin_data->get( 'language_api' ) );

		$hreflang_type = Mlp_Hreflang_Header_Output::TYPE_HTTP_HEADER | Mlp_Hreflang_Header_Output::TYPE_HTML_LINK_TAG;
		/**
		 * Filters the output type for the hreflang links.
		 *
		 * The type is a bitmask with possible (partial) values Mlp_Hreflang_Header_Output::TYPE_HTTP_HEADER and Mlp_Hreflang_Header_Output::TYPE_HTML_LINK_TAG.
		 *
		 * @since 2.7.0
		 *
		 * @param int $hreflang_type The output type for the hreflang links.
		 */
		$hreflang_type = absint( apply_filters( 'multilingualpress.hreflang_type', $hreflang_type ) );

		if ( $hreflang_type & Mlp_Hreflang_Header_Output::TYPE_HTTP_HEADER ) {
			add_action( 'template_redirect', array( $hreflang, 'http_header' ), 11 );
		}

		if ( $hreflang_type & Mlp_Hreflang_Header_Output::TYPE_HTML_LINK_TAG ) {
			add_action( 'wp_head', array( $hreflang, 'wp_head' ) );
		}
	}

	/**
	 * @return void
	 */
	private function prepare_plugin_data() {

		$site_relations = $this->plugin_data->get( 'site_relations' );
		$table_list = new Mlp_Db_Table_List( $this->wpdb );

		$this->link_table = $this->wpdb->base_prefix . 'multilingual_linked';
		$this->plugin_file_path = $this->plugin_data->get( 'plugin_file_path' );
		$this->plugin_data->set( 'module_manager', new Mlp_Module_Manager( 'state_modules' ) );
		$this->plugin_data->set( 'site_manager', new Mlp_Module_Manager( 'inpsyde_multilingual' ) );
		$this->plugin_data->set( 'table_list', $table_list );
		$this->plugin_data->set( 'link_table', $this->link_table );
		$this->plugin_data->set(
			'content_relations',
			new Mlp_Content_Relations(
				$this->wpdb,
				$site_relations,
				new Mlp_Db_Table_Name( $this->link_table, $table_list )
			)
		);
		$this->plugin_data->set(
			'language_api',
			new Mlp_Language_Api(
				$this->plugin_data,
				'mlp_languages',
				$site_relations,
				$this->plugin_data->get( 'content_relations' ),
				$this->wpdb
			)
		);
		$this->plugin_data->set( 'assets', new Mlp_Assets( $this->plugin_data->get( 'locations' ) ) );
	}

	/**
	 * @return void
	 */
	private function prepare_helpers() {

		Mlp_Helpers::$link_table = $this->link_table;
		Mlp_Helpers::insert_dependency( 'site_relations', $this->plugin_data->get( 'site_relations' ) );
		Mlp_Helpers::insert_dependency( 'language_api', $this->plugin_data->get( 'language_api' ) );
		Mlp_Helpers::insert_dependency( 'plugin_data', $this->plugin_data );
	}

}
