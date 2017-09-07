<?php # -*- coding: utf-8 -*-
/**
 * ${CARET}
 *
 * @version 2014.09.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

/**
 * Translation object
 *
 * @version 2014.09.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Translation_Interface {

	/**
	 * @return int
	 */
	public function get_source_site_id();

	/**
	 * @return string
	 */
	public function get_page_type();

	/**
	 * @return Mlp_Url_Interface
	 */
	public function get_icon_url();

	/**
	 * @return int
	 */
	public function get_target_site_id();

	/**
	 * @return string
	 */
	public function get_target_title();

	/**
	 * @return Mlp_Language_Interface
	 */
	public function get_language();

	/**
	 * @return int
	 */
	public function get_target_content_id();

	/**
	 * @return string
	 */
	public function get_remote_url();
}
