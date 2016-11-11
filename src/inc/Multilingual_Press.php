<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\Core;
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

		// Update network option.
		$blogs = get_network_option( null, 'inpsyde_multilingual', [] );
		if ( isset( $blogs[ $blog_id ] ) ) {
			unset( $blogs[ $blog_id ] );
			update_site_option( 'inpsyde_multilingual', $blogs );
		}

		$table = $this->container['multilingualpress.content_relations_table']->name();

		// Clean up linked elements table
		$sql = "
			DELETE
			FROM {$table}
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
		$all_blogs = (array) get_site_option( 'inpsyde_multilingual', [] );

		if ( 1 > count( $all_blogs ) && is_super_admin() )
			return TRUE;

		return FALSE;
	}

	/**
	 * @return void
	 */
	private function run_admin_actions() {

		$setting = new Mlp_Network_Site_Settings_Tab_Data( $this->plugin_data->get( 'type_factory' ) );

		$nonce = new WPNonce( $setting->action() );

		// TODO: Check what this sucker needs...
		new Mlp_Network_Site_Settings_Controller( $this->plugin_data, $setting, $nonce );

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

		// TODO: Move the following block to ~\Core\CoreServiceProvider as soon as the Language API has been refactored.
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
	public function prepare_plugin_data() {

		$site_relations = $this->container['multilingualpress.site_relations'];

		$content_relations = $this->container['multilingualpress.content_relations'];

		$type_factory = $this->container['multilingualpress.type_factory'];

		$language_api = new Mlp_Language_Api(
			$this->plugin_data,
			'mlp_languages',
			$site_relations,
			$content_relations,
			$this->wpdb,
			$type_factory
		);

		$this->plugin_data->set( 'module_manager', $this->container['multilingualpress.module_manager'] );
		$this->plugin_data->set( 'site_relations', $site_relations );
		$this->plugin_data->set( 'type_factory', $type_factory );
		$this->plugin_data->set( 'link_table', $this->container['multilingualpress.content_relations_table']->name() );
		$this->plugin_data->set( 'content_relations', $content_relations );
		$this->plugin_data->set( 'language_api', $language_api );
		// TODO: Remove as soon as the whole Assets structures have been refactored (Locations -> Assets\Locator).
		$this->plugin_data->set( 'assets', $this->container['multilingualpress.asset_manager'] );
		$this->plugin_data->set( 'locations', $this->container['multilingualpress.internal_locations'] );
		$this->plugin_data->set( 'nonce_factory', $this->container['multilingualpress.nonce_factory'] );

		Mlp_Helpers::insert_dependency( 'language_api', $this->plugin_data->get( 'language_api' ) );
	}
}
