<?php # -*- coding: utf-8 -*-
/**
 * Simple parsing algorithm for HTTP Accept header strings
 *
 * @version    2014.09.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */

/**
 * Read an accept header and sort its values by priority.
 *
 * @version 2014.09.25
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Accept_Header_Parser_Interface {

	/**
	 * @param  string $accept_header
	 * @return array
	 */
	public function parse( $accept_header );
}
