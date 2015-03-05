<?php
/**
 * Check the current system if it matches the minimum requirements.
 *
 * @version 2014.08.29
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Requirements_Check implements Mlp_Requirements_Check_Interface {

	/**
	 * @var Mlp_Requirements_Interface
	 */
	private $requirements;

	/**
	 * @var Mlp_Version_Number_Interface
	 */
	private $current_php_version;

	/**
	 * @var Mlp_Version_Number_Interface
	 */
	private $current_wp_version;

	/**
	 * Directory of the plugin to check.
	 *
	 * @var string
	 */
	private $current_plugin_file;

	/**
	 * What went wrong?
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * @param Mlp_Requirements_Interface   $requirements
	 * @param Mlp_Version_Number_Interface $current_php_version
	 * @param Mlp_Version_Number_Interface $current_wp_version
	 * @param string                       $current_plugin_file
	 */
	public function __construct(
		Mlp_Requirements_Interface   $requirements,
		Mlp_Version_Number_Interface $current_php_version,
		Mlp_Version_Number_Interface $current_wp_version,
		                             $current_plugin_file
	) {

		$this->requirements        = $requirements;
		$this->current_php_version = $current_php_version;
		$this->current_wp_version  = $current_wp_version;
		$this->current_plugin_file = $this->normalize_path( $current_plugin_file );
	}

	/**
	 * Check all given requirements.
	 *
	 * @return bool
	 */
	public function is_compliant() {

		$this->check_php_version();
		$this->check_wp_version();
		$this->check_installation();

		if ( $this->requirements->network_activation_required() && is_multisite() )
			$this->check_activation();

		return empty ( $this->errors );
	}

	/**
	 * @return array
	 */
	public function get_error_messages() {
		return $this->errors;
	}

	/**
	 * Check PHP version.
	 *
	 * @return void
	 */
	private function check_php_version() {

		$current  = $this->current_php_version;
		$required = $this->requirements->get_min_php_version();

		if ( version_compare( $current, $required, '>=' ) )
			return;

		$msg = esc_html_x(
			'This plugin requires PHP version %1$s, your version %2$s is too old. Please upgrade.',
			'1 = required PHP version, 2 = current',
			'multilingualpress'
		);
		$this->errors[ 'php' ] = sprintf( $msg, $required, $current );
	}

	/**
	 * @return void
	 */
	private function check_wp_version() {

		$current  = $this->current_wp_version;
		$required = $this->requirements->get_min_wp_version();

		if ( version_compare( $current, $required, '>=' ) )
			return;

		$msg = esc_html_x(
			'This plugin requires WordPress version %1$s, your version %2$s is too old. Please upgrade.',
			'1 = required WordPress version, 2 = current',
			'multilingualpress'
		);
		$this->errors[ 'wp' ] = sprintf( $msg, $required, $current );
	}

	/**
	 * Make all slashes unique.
	 *
	 * @param  string $path
	 * @return string
	 */
	private function normalize_path( $path ) {

		// WP 3.9 and newer.
		if ( function_exists( 'wp_normalize_path' ) )
			return wp_normalize_path( $path );

		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|/+|','/', $path );

		return $path;
	}

	/**
	 * Checks for a required multisite installation.
	 *
	 * @return void
	 */
	private function check_installation() {

		if ( ! $this->requirements->multisite_required() )
			return;

		if ( is_multisite() )
			return;

		$msg = _x(
			'This plugin needs to run in a multisite. Please <a href="%s">convert this WordPress installation to multisite</a>.',
			'%s = link to installation instructions',
			'multilingualpress'
		);
		$this->errors[ 'installation' ] = sprintf(
			$msg,
			'http://make.marketpress.com/multilingualpress/2014/02/how-to-install-multi-site/'
		);
	}

	/**
	 * Checks for a required network activation.
	 *
	 * @return void
	 */
	private function check_activation() {

		$plugins = wp_get_active_network_plugins();

		foreach ( $plugins as $plugin ) {

			$plugin_file = $this->normalize_path( $plugin );

			if ( $this->current_plugin_file === $plugin_file )
				return;
		}

		$msg = _x(
			'This plugin must be activated for the network. Please use the <a href="%s">network plugin administration</a>.',
			'%s = link to network plugin screen',
			'multilingualpress'
		);
		$url = network_admin_url( 'plugins.php' );
		$this->errors[ 'activation' ] = sprintf( $msg, $url );
	}
}