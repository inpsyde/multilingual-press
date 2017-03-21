<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Factory\TypeFactory;
use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\MultilingualPress;

/**
 * MultilingualPress Installation checker.
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
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @var PluginProperties
	 */
	private $properties;

	/**
	 * Constructor.
	 *
	 * @param SystemChecker    $checker
	 * @param PluginProperties $properties
	 * @param TypeFactory      $type_factory
	 */
	public function __construct( SystemChecker $checker, PluginProperties $properties, TypeFactory $type_factory ) {

		$this->checker      = $checker;
		$this->type_factory = $type_factory;
		$this->properties   = $properties;
	}

	/**
	 * @return int
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

		$check_result = $this->checker->check_version( $installed_version, $current_version );

		update_network_option( null, MultilingualPress::VERSION_OPTION, $current_version );

		do_action( SystemChecker::ACTION_AFTER_CHECK, $check_result, $installed_version );

		return $installation_check;
	}

	/**
	 * @return VersionNumber[]
	 */
	private function get_versions(): array {

		$option = get_network_option( null, MultilingualPress::VERSION_OPTION );

		$installed_version = $this->type_factory->create_version_number( $option );
		$current_version   = $this->type_factory->create_version_number( [ $this->properties->version() ] );

		return [ $installed_version, $current_version ];

	}
}
