<?php

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
	 * @return Mlp_Semantic_Version_Number
	 */
	public function get_min_php_version() {

		return Mlp_Semantic_Version_Number_Factory::create( '5.2.4' );
	}

	/**
	 * Return the currently required WordPress version.
	 *
	 * @return Mlp_Semantic_Version_Number
	 */
	public function get_min_wp_version() {

		return Mlp_Semantic_Version_Number_Factory::create( '4.2.0' );
	}

	/**
	 * @return bool
	 */
	public function multisite_required() {

		return true;
	}

	/**
	 * @return bool
	 */
	public function network_activation_required() {

		return true;
	}

}
