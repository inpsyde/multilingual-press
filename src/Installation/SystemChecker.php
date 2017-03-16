<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Factory\TypeFactory;

/**
 * Performs various system-specific checks.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class SystemChecker {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_FORCE_CHECK = 'multilingualpress.force_system_check';

	/**
	 * Installation check status.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const WRONG_PAGE_FOR_CHECK = 1;

	/**
	 * Installation check status.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const INSTALLATION_OK = 2;

	/**
	 * Installation check status.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const PLUGIN_DEACTIVATED = 3;

	/**
	 * Version check status.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const VERSION_OK = 4;

	/**
	 * Version check status.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const NEEDS_INSTALLATION = 5;

	/**
	 * Version check status.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const NEEDS_UPGRADE = 6;

	/**
	 * Required minimum PHP version.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const MINIMUM_PHP_VERSION = '5.4.0';

	/**
	 * Required minimum WordPress version.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const MINIMUM_WORDPRESS_VERSION = '4.7.0';

	/**
	 * @var string[]
	 */
	private $errors = [];

	/**
	 * @var PluginProperties
	 */
	private $plugin_properties;

	/**
	 * @var SiteRelationsChecker
	 */
	private $site_relations_checker;

	/**
	 * @var SiteSettingsRepository
	 */
	private $site_settings_repository;

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PluginProperties       $plugin_properties        Plugin properties object.
	 * @param TypeFactory            $type_factory             Type factory object.
	 * @param SiteRelationsChecker   $site_relations_checker   Site relations checker object.
	 * @param SiteSettingsRepository $site_settings_repository Site settings repository object.
	 */
	public function __construct(
		PluginProperties $plugin_properties,
		TypeFactory $type_factory,
		SiteRelationsChecker $site_relations_checker,
		SiteSettingsRepository $site_settings_repository
	) {

		$this->plugin_properties = $plugin_properties;

		$this->type_factory = $type_factory;

		$this->site_relations_checker = $site_relations_checker;

		$this->site_settings_repository = $site_settings_repository;
	}

	/**
	 * Checks the installation for compliance with the system requirements.
	 *
	 * @since 3.0.0
	 *
	 * @return int The status of the installation check.
	 */
	public function check_installation(): int {

		/**
		 * Filters if the system check should be forced regardless of the context.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $force Whether or not the system check should be forced
		 */
		$force_check = (bool) apply_filters( static::ACTION_FORCE_CHECK, false );

		if ( ! $force_check && ! $this->is_context_valid() ) {
			return static::WRONG_PAGE_FOR_CHECK;
		}

		$this->check_php_version();

		$this->check_wordpress_version();

		$this->check_multisite();

		$this->check_plugin_activation();

		if ( ! $this->errors ) {
			$this->site_relations_checker->check_relations();

			return static::INSTALLATION_OK;
		}

		$deactivator = new PluginDeactivator(
			$this->plugin_properties->plugin_base_name(),
			$this->plugin_properties->plugin_name(),
			$this->errors
		);

		add_action( 'admin_notices', [ $deactivator, 'deactivate_plugin' ], 0 );
		add_action( 'network_admin_notices', [ $deactivator, 'deactivate_plugin' ], 0 );

		return static::PLUGIN_DEACTIVATED;
	}

	/**
	 * Checks the installed plugin version.
	 *
	 * @since 3.0.0
	 *
	 * @param VersionNumber $installed_version Installed MultilingualPress version.
	 * @param VersionNumber $current_version   Current MultilingualPress version.
	 *
	 * @return int The status of the version check.
	 */
	public function check_version( VersionNumber $installed_version, VersionNumber $current_version ): int {

		if ( version_compare( $installed_version, $current_version, '>=' ) ) {
			return static::VERSION_OK;
		}

		if ( ! $this->site_settings_repository->get_settings() ) {
			return static::NEEDS_UPGRADE;
		}

		return static::NEEDS_INSTALLATION;
	}

	/**
	 * Checks if the context is valid.
	 *
	 * @return bool Whether or not the context is valid.
	 */
	private function is_context_valid(): bool {

		if ( wp_doing_ajax() ) {
			return false;
		}

		if ( ! is_admin() ) {
			return false;
		}

		return 'plugins.php' === $GLOBALS['pagenow'];
	}

	/**
	 * Checks if the current PHP version is the required version higher, and collects potential error messages.
	 *
	 * @return void
	 */
	private function check_php_version() {

		$current_version = $this->type_factory->create_version_number( [
			PHP_VERSION,
		] );

		$required_version = $this->type_factory->create_version_number( [
			static::MINIMUM_PHP_VERSION,
		] );

		if ( version_compare( $current_version, $required_version, '>=' ) ) {
			return;
		}

		/* translators: 1: required PHP version, 2: current PHP version */
		$message = esc_html__(
			'This plugin requires PHP version %1$s, your version %2$s is too old. Please upgrade.',
			'multilingual-press'
		);

		$this->errors[] = sprintf( $message, $required_version, $current_version );
	}

	/**
	 * Checks if the current WordPress version is the required version higher, and collects potential error messages.
	 *
	 * @return void
	 */
	private function check_wordpress_version() {

		global $wp_version;

		$current_version = $this->type_factory->create_version_number( [
			$wp_version,
		] );

		$required_version = $this->type_factory->create_version_number( [
			static::MINIMUM_WORDPRESS_VERSION,
		] );

		if ( version_compare( $current_version, $required_version, '>=' ) ) {
			return;
		}

		/* translators: 1: required WordPress version, 2: current WordPress version */
		$message = esc_html__(
			'This plugin requires WordPress version %1$s, your version %2$s is too old. Please upgrade.',
			'multilingual-press'
		);

		$this->errors[] = sprintf( $message, $required_version, $current_version );
	}

	/**
	 * Checks if this is a multisite installation, and collects potential error messages.
	 *
	 * @return void
	 */
	private function check_multisite() {

		if ( is_multisite() ) {
			return;
		}

		/* translators: %s: link to installation instructions */
		$message = __(
			'This plugin needs to run in a multisite. Please <a href="%s">convert this WordPress installation to multisite</a>.',
			'multilingual-press'
		);

		$this->errors[] = sprintf( $message, 'http://make.multilingualpress.org/2014/02/how-to-install-multi-site/' );
	}

	/**
	 * Checks if MultilingualPress has been activated network-wide, and collects potential error messages.
	 *
	 * @return void
	 */
	private function check_plugin_activation() {

		$plugin_file_path = wp_normalize_path( realpath( $this->plugin_properties->plugin_file_path() ) );

		foreach ( wp_get_active_network_plugins() as $plugin ) {
			if ( $plugin_file_path === wp_normalize_path( realpath( $plugin ) ) ) {
				return;
			}
		}

		/* translators: %s: link to network plugin screen */
		$message = __(
			'This plugin must be activated for the network. Please use the <a href="%s">network plugin administration</a>.',
			'multilingual-press'
		);

		$this->errors[] = sprintf( $message, esc_url( network_admin_url( 'plugins.php' ) ) );
	}
}
