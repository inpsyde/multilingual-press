<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Admin\ActionLink;
use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\Core;
use Inpsyde\MultilingualPress\Database\WPDBTableList;
use Inpsyde\MultilingualPress\Factory\Error;
use Inpsyde\MultilingualPress\Service\Container;

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
	 * @var Container
	 */
	private $container;

	/**
	 * @var Mlp_Plugin_Properties
	 */
	private $plugin_data;

	/**
	 * @var PluginProperties
	 */
	private $properties;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {

		global $wpdb;

		$this->container = $container;

		$this->properties = $container['multilingualpress.properties'];

		// This thing is only to keep this main controller working.
		$this->plugin_data = new Mlp_Plugin_Properties();

		$this->wpdb = $wpdb;
	}

	/**
	 * Initial setup handler.
	 *
	 * @global	$wpdb wpdb WordPress Database Wrapper
	 * @global	$pagenow string Current Page Wrapper
	 * @return bool
	 */
	public function setup() {

		$this->prepare_plugin_data();
		$this->load_assets();
		$this->prepare_helpers();

		if ( ! $this->is_active_site() )
			return false;

		// Hooks and filters
		add_action( 'inpsyde_mlp_loaded', [ $this, 'load_plugin_textdomain' ], 1 );

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
		add_filter( 'delete_blog', [ $this, 'delete_blog' ], 10, 2 );

		// Check for errors
		add_filter( 'all_admin_notices', [ $this, 'check_for_user_errors_admin_notice' ] );

		add_action( 'wp_loaded', [ $this, 'late_load' ], 0 );

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

		return true;
	}

	/**
	 * Check if the current context needs more MultilingualPress actions.
	 *
	 * @return bool
	 */
	private function is_active_site() {

		global $pagenow;

		if ( in_array( $pagenow, [ 'admin-post.php', 'admin-ajax.php' ], true ) ) {
			return true;
		}

		if ( is_network_admin() )
			return TRUE;

		$relations = get_site_option( 'inpsyde_multilingual', [] );

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

		$rel_path = dirname( $this->properties->plugin_base_name() ) . $this->properties->text_domain_path();

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

		$assets->add( 'mlp-admin', 'admin.js', [ 'backbone' ], [
			'mlpSettings' => [ 'urlRoot' => $admin_url, ],
		] );

		$assets->add( 'mlp_admin_css', 'admin.css' );

		$assets->add( 'mlp-frontend', 'frontend.js' );

		$assets->add( 'mlp_frontend_css', 'frontend.css' );

		add_action( 'init', [ $assets, 'register' ], 0 );
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
		add_action( 'plugins_loaded', [ $settings, 'setup' ], 8 );

		// TODO: Don't hard-code URL.
		$settings_page_url = network_admin_url( 'settings.php?page=mlp' );

		( new ActionLink(
			'settings',
			'<a href="' . esc_url( $settings_page_url ) . '">' . __( 'Settings', 'multilingual-press' ) . '</a>'
		) )->register( 'network_admin_plugin_action_links_' . $this->properties->plugin_base_name() );
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
		add_action( 'plugins_loaded', [ $settings, 'setup' ], 8 );
	}

	/**
	 * Find and load core and pro features.
	 *
	 * @access	public
	 * @since	0.1
	 * @return	array Files to include
	 */
	protected function load_features() {

		$found = [];

		$path = $this->properties->plugin_dir_path() . '/src/inc';

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
		$blogs = (array) get_site_option( 'inpsyde_multilingual', [] );
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

		$site_language = \Inpsyde\MultilingualPress\get_current_site_language();
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
		$all_blogs = get_site_option( 'inpsyde_multilingual', [] );

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

		// TODO: Check what this sucker needs...
		new Mlp_Network_Site_Settings_Controller( $this->plugin_data );

		new Mlp_Network_New_Site_Controller(
			$this->plugin_data->get( 'language_api' ),
			$this->plugin_data->get( 'site_relations' )
		);
	}

	/**
	 * @return void
	 */
	private function run_frontend_actions() {

		// Use correct language for html element
		add_filter( 'language_attributes', [ $this, 'language_attributes' ] );

		$translations = new Core\FrontEnd\AlternateLanguages\UnfilteredTranslations(
			$this->plugin_data->get( 'language_api' )
		);
		add_action( 'template_redirect', function () use ( $translations ) {

			( new Core\FrontEnd\AlternateLanguages\HTTPHeaders( $translations ) )->send();
		} );
		add_action( 'wp_head', function () use ( $translations ) {

			( new Core\FrontEnd\AlternateLanguages\HTMLLinkTags( $translations ) )->render();
		} );
	}

	/**
	 * @return void
	 */
	private function prepare_plugin_data() {

		$type_factory = $this->container['multilingualpress.type_factory'];

		$site_relations = new \Mlp_Site_Relations( 'mlp_site_relations' );

		$this->plugin_data->set( 'site_relations', $site_relations );

		$this->plugin_data->set( 'type_factory', $type_factory );

		$table_list = new WPDBTableList( $this->wpdb );

		$link_table = $this->wpdb->base_prefix . 'multilingual_linked';
		$this->plugin_data->set( 'module_manager', new Mlp_Module_Manager( 'state_modules' ) );
		$this->plugin_data->set( 'site_manager', new Mlp_Module_Manager( 'inpsyde_multilingual' ) );
		$this->plugin_data->set( 'table_list', $table_list );
		$this->plugin_data->set( 'link_table', $link_table );
		$this->plugin_data->set(
			'content_relations',
			new Mlp_Content_Relations(
				$this->wpdb,
				$site_relations,
				null,
				$link_table
			)
		);
		$this->plugin_data->set(
			'language_api',
			new Mlp_Language_Api(
				$this->plugin_data,
				'mlp_languages',
				$site_relations,
				$this->plugin_data->get( 'content_relations' ),
				$this->wpdb,
				$type_factory
			)
		);

		// TODO: Remove as soon as the whole Assets structures have been refactored (Locations -> Assets\Locator).
		$plugin_dir_path = $this->properties->plugin_dir_path();
		$plugin_dir_url = $this->properties->plugin_dir_url();
		$locations = new Mlp_Internal_Locations();
		$locations->add_dir( $plugin_dir_path, $plugin_dir_url, 'plugin' );
		$locations->add_dir( "$plugin_dir_path/assets/css", "$plugin_dir_url/assets/css", 'css' );
		$locations->add_dir( "$plugin_dir_path/assets/js", "$plugin_dir_url/assets/js", 'js' );
		$locations->add_dir( "$plugin_dir_path/assets/images", "$plugin_dir_url/assets/images", 'images' );
		$locations->add_dir( "$plugin_dir_path/assets/images/flags", "$plugin_dir_url/assets/images/flags", 'flags' );
		$this->plugin_data->set( 'assets', new Mlp_Assets(
			$locations,
			$type_factory
		) );
		$this->plugin_data->set( 'locations', $locations );

		$this->plugin_data->set( 'error_factory', new Error() );
	}

	/**
	 * @return void
	 */
	private function prepare_helpers() {

		Mlp_Helpers::insert_dependency( 'site_relations', $this->plugin_data->get( 'site_relations' ) );
		Mlp_Helpers::insert_dependency( 'language_api', $this->plugin_data->get( 'language_api' ) );
		Mlp_Helpers::insert_dependency( 'error_factory', $this->plugin_data->get( 'error_factory' )  );
	}

}
