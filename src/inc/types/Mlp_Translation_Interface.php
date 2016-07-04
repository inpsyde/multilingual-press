<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\URL;

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
	 * @return URL URL instance.
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
	 * @return Language
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
