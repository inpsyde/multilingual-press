<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\MultilingualPress;

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

		// Cleanup upon blog delete
		add_action( 'delete_blog', [ $this, 'delete_blog' ], 10, 2 );

		// Check for errors
		add_action( 'all_admin_notices', [ $this, 'check_for_user_errors_admin_notice' ] );

		/**
		 * Runs after internal actions have been registered.
		 */
		do_action( 'inpsyde_mlp_loaded' );

		if ( is_admin() ) {
			$this->run_admin_actions();
		}

		return true;
	}

	/**
	 * Find and load core and pro features.
	 *
	 * @return array Files to include
	 */
	protected function load_features() {

		$path = $this->properties->plugin_dir_path() . '/src/inc';
		if ( ! is_dir( $path ) || ! is_readable( $path ) ) {
			return [];
		}

		$files = glob( "$path/feature.*.php" );
		foreach ( $files as $file ) {
			include_once $file;
		}

		return $files;
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
		MultilingualPress::resolve( 'multilingualpress.site_relations' )->delete_relation( $blog_id );

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

		$setting = new Mlp_Network_Site_Settings_Tab_Data(
			MultilingualPress::resolve( 'multilingualpress.type_factory' )
		);

		new Mlp_Network_Site_Settings_Controller( $setting, new WPNonce( $setting->action() ) );

		new Mlp_Network_New_Site_Controller(
			MultilingualPress::resolve( 'multilingualpress.site_relations' ),
			MultilingualPress::resolve( 'multilingualpress.languages' )
		);
	}

	/**
	 * @return void
	 */
	public function prepare_plugin_data() {

		new Mlp_Language_Manager_Controller(
			new Mlp_Language_Db_Access( $this->container['multilingualpress.languages_table']->name() ),
			$this->wpdb
		);
	}
}
