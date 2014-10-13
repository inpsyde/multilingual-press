<?php
/**
 * Handle scripts and stylesheets
 *
 * @version 2014.10.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Assets implements Mlp_Assets_Interface {

	/**
	 * @type Mlp_Locations_Interface
	 */
	private $locations;

	/**
	 * List of registered resources
	 *
	 * @type array
	 */
	private $registered = array();

	/**
	 * @type array
	 */
	private $assets;

	/**
	 * @param Mlp_Locations_Interface $locations
	 */
	public function __construct( Mlp_Locations_Interface $locations ) {

		$this->locations = $locations;
	}

	/**
	 * @param  string $handle
	 * @param  string $file
	 * @param  array  $dependencies
	 * @return bool
	 */
	public function add( $handle, $file, $dependencies = array() ) {

		$ext = $this->get_extension( $file );

		if ( ! $this->locations->has_dir( $ext ) )
			return FALSE;

		$url = new Mlp_Asset_Url( $file,
			$this->locations->get_dir( $ext, 'path' ),
			$this->locations->get_dir( $ext, 'url' )
		);
		$this->assets[ $handle ] = array(
			'url'          => $url,
			'ext'          => $ext,
			'dependencies' => $dependencies
		);

		return TRUE;
	}

	/**
	 * @wp-hook wp_loaded
	 * @return void
	 */
	public function register() {

		foreach ( $this->assets as $handle => $properties ) {

			if ( ! in_array( $properties[ 'ext' ], array ( 'js', 'css' ) ) )
				continue;

			/** @type Mlp_Asset_Url_Interface $url_object  */
			$url_object = $properties[ 'url' ];
			$url        = $url_object->__toString();
			$version    = $url_object->get_version();

			if ( 'js' === $properties[ 'ext' ] ) {
				wp_register_script(
					$handle,
					$url,
					$properties[ 'dependencies' ],
					$version,
					TRUE
				);
			}

			if ( 'css' === $properties[ 'ext' ] ) {
				wp_register_style(
					$handle,
					$url,
					$properties[ 'dependencies' ],
					$version
				);
			}

			$this->registered[ $handle ] = $properties[ 'ext' ];
		}
	}

	/**
	 * @param $handles
	 * @return bool
	 */
	public function provide( $handles ) {

		$to_load = $this->get_valid_handles( (array) $handles );

		if ( empty ( $to_load ) )
			return FALSE;

		$action = $this->get_enqueue_action();

		if ( '' === $action )
			return FALSE;

		$loader = new Mlp_Asset_Loader( $to_load );

		add_action( $action, array ( $loader, 'enqueue' ) );

		return TRUE;
	}

	/**
	 * @param array $handles
	 * @return array
	 */
	private function get_valid_handles( Array $handles ) {

		$to_load = array();

		foreach ( $handles as $handle ) {
			if ( ! empty ( $this->registered[ $handle ] ) )
				$to_load[ $handle ] = $this->registered[ $handle ];
		}

		return $to_load;
	}

	/**
	 * @return string
	 */
	private function get_enqueue_action() {

		if ( $this->is_login_page() ) {

			if ( empty ( $GLOBALS[ 'interim_login' ] ) )
				return 'login_enqueue_scripts';

			return '';
		}

		if ( is_admin() )
			return 'admin_enqueue_scripts';

		if ( is_customize_preview() )
			return 'customize_controls_enqueue_scripts';

		return 'wp_enqueue_scripts';
	}

	/**
	 * @return bool
	 */
	private function is_login_page() {

		return 0 === strpos( $_SERVER['REQUEST_URI' ], '/wp-login.php' );
	}

	/**
	 * Get the file extension.
	 *
	 * @param  string $file_name
	 * @return string
	 */
	private function get_extension( $file_name ) {

		$last_dot = strrchr( $file_name, '.' );

		if ( FALSE === $last_dot )
			return '_invalid_';

		return substr( $last_dot, 1 );
	}
}