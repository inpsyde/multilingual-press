<?php # -*- coding: utf-8 -*-
/**
 * Set up auto-loader or load all available files immediately for PHP < 5.3.
 *
 * @author     toscho
 * @since      2013.08.18
 * @version    2013.08.22
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package    MultilingualPress
 * @subpackage Autoload
 */
class Mlp_Load_Controller {

	/**
	 * Path to Inpsyde Suite files
	 *
	 * @type string
	 */
	protected $plugin_dir;

	protected $loader;

	/**
	 * Constructor
	 *
	 * @param string $plugin_dir
	 */
	public function __construct( $plugin_dir ) {

		$this->plugin_dir = $plugin_dir;

		// Can be turned off in PHP 5.2
		if ( function_exists( 'spl_autoload_register' ) )
			$this->setup_autoloader();
		else
			$this->setup_compat_loader();
	}

	/**
	 * Real auto-loader for modern PHP installations.
	 *
	 * @return void
	 */
	protected function setup_autoloader() {

		$dir = dirname( __FILE__ );

		// We need these classes in exactly this order
		if ( ! interface_exists( 'Inpsyde_Autoload_Rule_Interface' ) )
			require "$dir/Inpsyde_Autoload_Rule_Interface.php";

		if ( ! class_exists( 'Mlp_Autoload_Rule' ) )
			require "$dir/Mlp_Autoload_Rule.php";

		if ( ! class_exists( 'Inpsyde_Autoload' ) )
			require "$dir/Inpsyde_Autoload.php";

		$this->loader = new Inpsyde_Autoload;
		$rule         = new Mlp_Autoload_Rule( $this->plugin_dir );
		$this->loader->add_rule( $rule );
	}

	/**
	 * Return current instance of autoloader.
	 *
	 * @return Inpsyde_Autoload $this->loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Fallback autoloader for PHP 5.2 with SPL turned off.
	 * FIXME Test compat loader
	 *
	 * @return void
	 */
	protected function setup_compat_loader() {

		$base     = $this->plugin_dir;
		$features = "$this->plugin_dir/features";
		$walker   = array ( $this, 'load_files' );

		// must be loaded first
		require_once "$base/interfaces/Inpsyde_Property_List_Interface.php";
		//require_once "$this->plugin_dir/interfaces/Inpsyde_Options_Interface.php";
		require_once "$base/classes/Inpsyde_Property_List.php";

		$interfaces = glob( "$base/interfaces/*.php" );
		array_walk( $interfaces, $walker, 'interface' );

		$classes    = glob( "$base/classes/*.php" );
		array_walk( $classes, $walker, 'class' );

		if ( ! is_dir( $features ) )
			return;

		$feature_interface_dir = "$features/interfaces";
		$feature_class_dir     = "$features/classes";

		if ( is_dir( $feature_interface_dir ) ) {
			$feature_interfaces = glob( "$feature_interface_dir/*.php" );
			array_walk( $feature_interfaces, $walker, 'interface' );
		}

		if ( is_dir( $feature_class_dir ) ) {
			$feature_classes = glob( "$feature_class_dir/*.php" );
			array_walk( $feature_classes, $walker, 'class' );
		}
	}

	protected function load_files( $path, $key, $type ) {

		$func = $type . '_exists';
		$name = basename( $path, '.php' );
		$func( $name ) or require $path;
	}
}