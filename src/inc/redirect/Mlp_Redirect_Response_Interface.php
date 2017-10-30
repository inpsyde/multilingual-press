<?php # -*- coding: utf-8 -*-
/**
 * Send redirect headers.
 *
 * @version    2014.04.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
interface Mlp_Redirect_Response_Interface {

	/**
	 * Redirect if needed.
	 *
	 * @return void
	 */
	public function redirect();

	/**
	 * Registers the redirection using the appropriate hook.
	 *
	 * @return void
	 */
	public function register();
}
