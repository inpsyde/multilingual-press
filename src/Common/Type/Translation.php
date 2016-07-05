<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Interface for all translation data type implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
interface Translation {

	/**
	 * Returns the icon URL object.
	 *
	 * @since 3.0.0
	 *
	 * @return URL Icon URL object.
	 */
	public function get_icon_url();

	/**
	 * Returns the language object.
	 *
	 * @since 3.0.0
	 *
	 * @return Language Language object.
	 */
	public function get_language();

	/**
	 * Returns the remote title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote title.
	 */
	public function get_remote_title();

	/**
	 * Returns the remote URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote URL.
	 */
	public function get_remote_url();

	/**
	 * Returns the source site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Source site ID.
	 */
	public function get_source_site_id();

	/**
	 * Returns the target content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target content ID.
	 */
	public function get_target_content_id();

	/**
	 * Returns the target site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target site ID.
	 */
	public function get_target_site_id();

	/**
	 * Returns the content type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Content type.
	 */
	public function get_type();
}
