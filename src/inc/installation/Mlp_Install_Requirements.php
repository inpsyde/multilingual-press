<?php

use Inpsyde\MultilingualPress\Common\Factory\TypeFactory;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;

/**
 * Details of the currently required PHP and WordPress versions.
 *
 * @version 2015.07.01
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Install_Requirements implements Mlp_Requirements_Interface {

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @param TypeFactory $type_factory Type factory object.
	 */
	public function __construct( TypeFactory $type_factory ) {

		$this->type_factory = $type_factory;
	}

	/**
	 * Return the currently required PHP version.
	 *
	 * @return VersionNumber
	 */
	public function get_min_php_version() {

		return $this->type_factory->create_version_number( [
			'5.4.0',
		] );
	}

	/**
	 * Return the currently required WordPress version.
	 *
	 * @return VersionNumber
	 */
	public function get_min_wp_version() {

		return $this->type_factory->create_version_number( [
			'4.2.0',
		] );
	}

	/**
	 * @return bool
	 */
	public function multisite_required() {

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function network_activation_required() {

		return TRUE;
	}

}
