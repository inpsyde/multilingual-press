<?php
/**
 * Interface for URLs with a file version.
 *
 * @version 2014.10.07
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */


interface Mlp_Asset_Url_Interface extends Mlp_Url_Interface {

	/**
	 * @return string
	 */
	public function get_version();
}