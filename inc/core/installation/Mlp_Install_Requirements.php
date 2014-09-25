<?php
/**
 * Details of the currently required PHP and WordPress versions.
 *
 * @version 2014.08.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Install_Requirements implements Mlp_Requirements_Interface {

	/**
	 * @return Mlp_Semantic_Version_Number
	 */
	public function get_min_php_version() {
		return new Mlp_Semantic_Version_Number( '5.2.4' );
	}

	/**
	 * @return Mlp_Semantic_Version_Number
	 */
	public function get_min_wp_version() {
		return new Mlp_Semantic_Version_Number( '3.9.0' );
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