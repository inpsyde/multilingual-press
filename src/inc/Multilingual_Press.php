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
