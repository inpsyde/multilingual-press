<?php

use Inpsyde\MultilingualPress\Common\Type\VersionNumber;

/**
 * Currently required PHP and WordPress versions.
 *
 * @version 2014.08.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Requirements_Interface {

	/**
	 * @return VersionNumber
	 */
	public function get_min_php_version();

	/**
	 * @return VersionNumber
	 */
	public function get_min_wp_version();

	/**
	 * @return bool
	 */
	public function multisite_required();

	/**
	 * @return bool
	 */
	public function network_activation_required();
}
