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
	 * @return string
	 */
	public function get_redirect_match();
}