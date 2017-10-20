<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Factory\TypeFactory;
use Inpsyde\MultilingualPress\MultilingualPress;

/**
 * MultilingualPress installation checker.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class InstallationChecker {

	/**
	 * @var SystemChecker
	 */
	private $checker;

	/**
	 * @var PluginProperties
	 */
	private $properties;

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SystemChecker    $checker      System checker object.
	 * @param PluginProperties $properties   Plugin properties object.
	 * @param TypeFactory      $type_factory Type factory object.
	 */
	public function __construct( SystemChecker $checker, PluginProperties $properties, TypeFactory $type_factory ) {

		$this->checker = $checker;

		$this->properties = $properties;

		$this->type_factory = $type_factory;
	}

	/**
	 * Checks the installation for compliance with the system requirements.
	 *
	 * @since 3.0.0
	 *
	 * @return int The status of the installation check.
	 */
	public function check(): int {

		$installation_check = $this->checker->check_installation();

		if (
			SystemChecker::PLUGIN_DEACTIVATED === $installation_check
			|| SystemChecker::INSTALLATION_OK !== $installation_check
		) {
			return $installation_check;
		}

		list( $installed_version, $current_version ) = $this->get_versions();

		$version_check = $this->checker->check_version( $installed_version, $current_version );

		/**
		 * Fires right after the MultilingualPress version check.
		 *
		 * @since 3.0.0
		 *
		 * @param int           $version_check     The status of the version check.
		 * @param VersionNumber $installed_version Installed MultilingualPress version.
		 */
		do_action( SystemChecker::ACTION_CHECKED_VERSION, $version_check, $installed_version );

		update_network_option( null, MultilingualPress::OPTION_VERSION, (string) $current_version );

		return $installation_check;
	}

	/**
	 * Returns an array with the installed and the current version of MultilingualPress.
	 *
	 * @return VersionNumber[] Version objects.
	 */
	private function get_versions(): array {

		$installed_version = $this->type_factory->create_version_number( [
			(string) get_network_option( null, MultilingualPress::OPTION_VERSION ),
		] );

		$current_version = $this->type_factory->create_version_number( [
			$this->properties->version(),
		] );

		return [ $installed_version, $current_version ];
	}
}
