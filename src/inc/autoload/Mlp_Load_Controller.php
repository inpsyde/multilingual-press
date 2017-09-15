<?php # -*- coding: utf-8 -*-
/**
 * Set up auto-loader.
 *
 * @author     toscho
 * @since      2013.08.18
 * @version    2014.09.28
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package    MultilingualPress
 * @subpackage Autoload
 */
class Mlp_Load_Controller {

	/**
	 * Path to plugin files
	 *
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * Instance of Inpsyde_Autoload
	 *
	 * @var Inpsyde_Autoload
	 */
	private $loader;

	/**
	 * Constructor
	 *
	 * @param string $plugin_dir
	 */
	public function __construct( $plugin_dir ) {

		$this->plugin_dir = $plugin_dir;

		// Can be turned off in PHP 5.2. We ignore that.
		$this->setup_autoloader();
	}

	/**
	 * Return current instance of autoloader.
	 *
	 * @return Inpsyde_Autoload
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Real auto-loader for modern PHP installations.
	 *
	 * @return void
	 */
	private function setup_autoloader() {

		$dir = dirname( __FILE__ );

		// We need these classes in exactly this order
		if ( ! interface_exists( 'Inpsyde_Autoload_Rule_Interface' ) ) {
			require "$dir/Inpsyde_Autoload_Rule_Interface.php";
		}

		foreach ( array( 'Directory_Load', 'Autoload' ) as $class ) {
			if ( ! class_exists( "Inpsyde_$class" ) ) {
				require "$dir/Inpsyde_$class.php";
			}
		}

		$this->loader = new Inpsyde_Autoload();
		$this->load_defaults( $this->loader );
	}

	/**
	 * Register default directories.
	 *
	 * Searches for child directories of /core/ and /pro/ and registers them
	 * for auto-loading.
	 *
	 * Cannot use `GLOB_BRACE`, because that is not available on SunOS.
	 *
	 * @param  Inpsyde_Autoload $loader
	 * @return void
	 */
	private function load_defaults( Inpsyde_Autoload $loader ) {

		$dirs = glob( "$this->plugin_dir/*", GLOB_ONLYDIR );

		foreach ( $dirs as $dir ) {
			$loader->add_rule( new Inpsyde_Directory_Load( $dir ) );
		}
	}
}
