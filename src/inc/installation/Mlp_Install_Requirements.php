<?php

use Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber;

/**
 * Details of the currently required PHP and WordPress versions.
 *
 * @version 2015.07.01
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Install_Requirements implements Mlp_Requirements_Interface {

	/**
	 * Return the currently required PHP version.
	 *
	 * @return SemanticVersionNumber
	 */
	public function get_min_php_version() {

		return SemanticVersionNumber::create( '5.4.0' );
	}

	/**
	 * Return the currently required WordPress version.
	 *
	 * @return SemanticVersionNumber
	 */
	public function get_min_wp_version() {

		return SemanticVersionNumber::create( '4.2.0' );
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
