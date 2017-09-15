<?php
/**
 * Data type for version numbers.
 *
 * @version 2014.08.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Version_Number_Interface {

	/**
	 * Used when validation fails.
	 *
	 * @type string
	 */
	const FALLBACK_VERSION = '0.0.0';

	/**
	 * @return string
	 */
	public function __toString();
}
