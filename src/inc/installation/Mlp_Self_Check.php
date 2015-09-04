<?php

/**
 * Applies some checks before the main code can run.
 *
 * Inspects the current context (WordPress and PHP),
 * and previous and competing installations.
 *
 *
 * @version 2014.09.03
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Self_Check {

	/**
	 * @type int
	 */
	const INSTALLATION_CONTEXT_OK = 1;

	/**
	 * @type int
	 */
	const WRONG_PAGE_FOR_CHECK = 2;

	/**
	 * @type int
	 */
	const PLUGIN_DEACTIVATED = 3;

	/**
	 * @type int
	 */
	const NEEDS_INSTALLATION = 4;

	/**
	 * @type int
	 */
	const NEEDS_UPGRADE = 5;

	/**
	 * @type int
	 */
	const NO_UPGRADE_NEEDED = 6;

	/**
	 * Path to plugin main file.
	 *
	 * @type string
	 */
	private $plugin_file;

	/**
	 * @var string
	 */
	private $pagenow;

	/**
	 * @param string $plugin_file
	 * @param string $pagenow
	 */
	public function __construct( $plugin_file, $pagenow ) {

		$this->plugin_file = $plugin_file;
		$this->pagenow = $pagenow;
	}

	/**
	 * Check if MultilingualPress was installed correctly.
	 *
	 * @param  string $name
	 * @param  string $base_name
	 * @param  string $wp_version
	 *
	 * @return string
	 */
	public function pre_install_check( $name, $base_name, $wp_version ) {

		if ( ! $this->is_plugin_page() ) {
			return self::WRONG_PAGE_FOR_CHECK;
		}

		$php_version = phpversion();

		$check = new Mlp_Requirements_Check(
			new Mlp_Install_Requirements(),
			new Mlp_Semantic_Version_Number( $php_version ),
			new Mlp_Semantic_Version_Number( $wp_version ),
			$this->plugin_file
		);

		if ( $check->is_compliant() ) {
			return self::INSTALLATION_CONTEXT_OK;
		}

		$errors = $check->get_error_messages();
		$deactivate = new Mlp_Plugin_Deactivation( $errors, $name, $base_name );

		add_action( 'admin_notices', array( $deactivate, 'deactivate' ), 0 );
		add_action( 'network_admin_notices', array( $deactivate, 'deactivate' ), 0 );

		return self::PLUGIN_DEACTIVATED;
	}

	/**
	 * Check if we need an upgrade for our tables.
	 *
	 * @param  Mlp_Version_Number_Interface $current_version
	 * @param  Mlp_Version_Number_Interface $last_version
	 *
	 * @return int
	 */
	public function is_current_version( Mlp_Version_Number_Interface $current_version, Mlp_Version_Number_Interface $last_version ) {

		if ( version_compare( $current_version, $last_version, '=<' ) ) {
			return self::NO_UPGRADE_NEEDED;
		}

		$mlp_settings = get_site_option( 'inpsyde_multilingual' );

		if ( empty ( $mlp_settings ) ) {
			return self::NEEDS_INSTALLATION;
		}

		return self::NEEDS_UPGRADE;
	}

	/**
	 * Test if we are on a page where we can run the checks.
	 *
	 * @return bool
	 */
	private function is_plugin_page() {

		if ( ! is_admin() ) {
			return FALSE;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return FALSE;
		}

		return 'plugins.php' === $this->pagenow;
	}

}
