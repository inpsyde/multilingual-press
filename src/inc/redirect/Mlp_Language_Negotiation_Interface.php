<?php # -*- coding: utf-8 -*-
/**
 * Mlp_Language_Negotiation_Interface
 *
 * @version    2014.09.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
interface Mlp_Language_Negotiation_Interface {

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_redirect_match( array $args = array() );

	/**
	 * Returns the redirect target data for all available language versions.
	 *
	 * @since 2.10.0
	 *
	 * @param array $args Optional. Arguments required to determine the redirect targets. Defaults to empty array.
	 *
	 * @return array[] Array of redirect targets.
	 */
	public function get_redirect_targets( array $args = array() );
}
