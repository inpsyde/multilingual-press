<?php # -*- coding: utf-8 -*-

/**
 * Check whether or not the current system matches the minimum requirements.
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
	 * @var string
	 */
	private $current_plugin_file;

	/**
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
		Mlp_Requirements_Interface $requirements,
		Mlp_Version_Number_Interface $current_php_version,
		Mlp_Version_Number_Interface $current_wp_version,
		$current_plugin_file
	) {

		$this->requirements = $requirements;

		$this->current_php_version = $current_php_version;

		$this->current_wp_version = $current_wp_version;

		if ( defined( 'MLP_PLUGIN_FILE' ) ) {
			$current_plugin_file = MLP_PLUGIN_FILE;
		}
		$current_plugin_file = realpath( $current_plugin_file );
		$this->current_plugin_file = $this->normalize_path( $current_plugin_file );
	}

	/**
	 * Make all slashes unique.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	private function normalize_path( $path ) {

		// WP 3.9 and newer.
		if ( function_exists( 'wp_normalize_path' ) ) {
			return wp_normalize_path( $path );
		}

		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|/+|', '/', $path );

		return $path;
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

		if ( $this->requirements->network_activation_required() && is_multisite() ) {
			$this->check_activation();
		}

		return empty( $this->errors );
	}

	/**
	 * Check PHP version.
	 *
	 * @return void
	 */
	private function check_php_version() {

		$current = $this->current_php_version;

		$required = $this->requirements->get_min_php_version();

		if ( version_compare( $current, $required, '>=' ) ) {
			return;
		}

		/* translators: 1: required PHP version, 2: current PHP version */
		$msg = esc_html__(
			'This plugin requires PHP version %1$s, your version %2$s is too old. Please upgrade.',
			'multilingual-press'
		);
		$this->errors['php'] = sprintf( $msg, $required, $current );
	}

	/**
	 * Check WordPress version.
	 *
	 * @return void
	 */
	private function check_wp_version() {

		$current = $this->current_wp_version;

		$required = $this->requirements->get_min_wp_version();

		if ( version_compare( $current, $required, '>=' ) ) {
			return;
		}

		/* translators: 1: required WordPress version, 2: current WordPress version */
		$msg = esc_html__(
			'This plugin requires WordPress version %1$s, your version %2$s is too old. Please upgrade.',
			'multilingual-press'
		);
		$this->errors['wp'] = sprintf( $msg, $required, $current );
	}

	/**
	 * Checks for a required multisite installation.
	 *
	 * @return void
	 */
	private function check_installation() {

		if ( ! $this->requirements->multisite_required() ) {
			return;
		}

		if ( is_multisite() ) {
			return;
		}

		/* translators: %s: link to installation instructions */
		$msg = __(
			'This plugin needs to run in a multisite. Please <a href="%s">convert this WordPress installation to multisite</a>.',
			'multilingual-press'
		);
		$this->errors['installation'] = sprintf(
			$msg,
			'http://make.multilingualpress.pro/2014/02/how-to-install-multi-site/'
		);
	}

	/**
	 * Checks for a required network activation.
	 *
	 * @return void
	 */
	private function check_activation() {

		foreach ( wp_get_active_network_plugins() as $plugin ) {
			$plugin_file = realpath( $plugin );

			if ( $this->current_plugin_file === $this->normalize_path( $plugin_file ) ) {
				return;
			}
		}

		/* translators: %s: link to network plugin screen */
		$msg = __(
			'This plugin must be activated for the network. Please use the <a href="%s">network plugin administration</a>.',
			'multilingual-press'
		);
		$url = network_admin_url( 'plugins.php' );
		$this->errors['activation'] = sprintf( $msg, $url );
	}

	/**
	 * Return all collected errors.
	 *
	 * @return array
	 */
	public function get_error_messages() {

		return $this->errors;
	}

}
