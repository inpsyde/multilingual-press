<?php

/**
 * Handle scripts and stylesheets
 *
 * @version 2015.07.06
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
class Mlp_Assets implements Mlp_Assets_Interface {

	/**
	 * @var Mlp_Locations_Interface
	 */
	private $locations;

	/**
	 * List of registered resources
	 *
	 * @var array
	 */
	private $registered = array();

	/**
	 * @var array
	 */
	private $assets = array();

	/**
	 * @var array
	 */
	private $l10n = array();

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Locations_Interface $locations Asset locations object.
	 */
	public function __construct( Mlp_Locations_Interface $locations ) {

		$this->locations = $locations;
	}

	/**
	 * Add an asset.
	 *
	 * @param string $handle       Unique handle.
	 * @param string $file         File.
	 * @param array  $dependencies Optional. Dependencies. Defaults to array().
	 * @param array  $l10n         Optional. Localized data. Defaults to array().
	 *
	 * @return bool
	 */
	public function add( $handle, $file, $dependencies = array(), $l10n = array() ) {

		$ext = $this->get_extension( $file );

		if ( ! $this->locations->has_dir( $ext ) ) {
			return false;
		}

		$url = new Mlp_Asset_Url(
			$file,
			$this->locations->get_dir( $ext, 'path' ),
			$this->locations->get_dir( $ext, 'url' )
		);

		$this->assets[ $handle ] = array(
			'url'          => $url,
			'ext'          => $ext,
			'dependencies' => $dependencies,
		);

		if ( $l10n ) {
			$this->l10n[ $handle ] = $l10n;
		}

		return true;
	}

	/**
	 * Register the assets.
	 *
	 * @wp-hook wp_loaded
	 *
	 * @return void
	 */
	public function register() {

		foreach ( $this->assets as $handle => $properties ) {
			if ( ! in_array( $properties['ext'], array( 'js', 'css' ), true ) ) {
				continue;
			}

			/** @var Mlp_Asset_Url_Interface $url_object */
			$url_object = $properties['url'];
			$url = $url_object->__toString();
			$version = $url_object->get_version();

			if ( 'js' === $properties['ext'] ) {
				wp_register_script(
					$handle,
					$url,
					$properties['dependencies'],
					$version,
					true
				);
			} elseif ( 'css' === $properties['ext'] ) {
				wp_register_style(
					$handle,
					$url,
					$properties['dependencies'],
					$version
				);
			}

			$this->registered[ $handle ] = $properties['ext'];
		}
	}

	/**
	 * Provide assets for the given handles.
	 *
	 * @param array|string $handles One or more asset handles.
	 *
	 * @return bool
	 */
	public function provide( $handles ) {

		$to_load = $this->get_valid_handles( (array) $handles );

		if ( empty( $to_load ) ) {
			return false;
		}

		$action = $this->get_enqueue_action();

		if ( '' === $action ) {
			return false;
		}

		$loader = new Mlp_Asset_Loader( $to_load, $this->l10n );

		add_action( $action, array( $loader, 'enqueue' ) );

		return true;
	}

	/**
	 * @param array $handles
	 *
	 * @return array
	 */
	private function get_valid_handles( array $handles ) {

		$to_load = array();

		foreach ( $handles as $handle ) {
			if ( ! empty( $this->registered[ $handle ] ) ) {
				$to_load[ $handle ] = $this->registered[ $handle ];
			}
		}

		return $to_load;
	}

	/**
	 * @return string
	 */
	private function get_enqueue_action() {

		if ( $this->is_login_page() ) {

			if ( empty( $GLOBALS['interim_login'] ) ) {
				return 'login_enqueue_scripts';
			}

			return '';
		}

		if ( is_admin() ) {
			return 'admin_enqueue_scripts';
		}

		if ( is_customize_preview() ) {
			return 'customize_controls_enqueue_scripts';
		}

		return 'wp_enqueue_scripts';
	}

	/**
	 * @return bool
	 */
	private function is_login_page() {

		return 0 === strpos( $_SERVER['REQUEST_URI'], '/wp-login.php' );
	}

	/**
	 * Get the file extension.
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	private function get_extension( $file_name ) {

		$last_dot = strrchr( $file_name, '.' );

		if ( false === $last_dot ) {
			return '_invalid_';
		}

		return substr( $last_dot, 1 );
	}

}
