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
	 * Filter hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_URL = 'multilingualpress.translation_url';

	/**
	 * Returns the icon URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Icon URL.
	 */
	public function icon_url(): string;

	/**
	 * Returns the language object.
	 *
	 * @since 3.0.0
	 *
	 * @return Language Language object.
	 */
	public function language(): Language;

	/**
	 * Returns the remote title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote title.
	 */
	public function remote_title(): string;

	/**
	 * Returns the remote URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote URL.
	 */
	public function remote_url(): string;

	/**
	 * Returns the source site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Source site ID.
	 */
	public function source_site_id(): int;

	/**
	 * Returns the target content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target content ID.
	 */
	public function target_content_id(): int;

	/**
	 * Returns the target site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target site ID.
	 */
	public function target_site_id(): int;

	/**
	 * Returns the content type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Content type.
	 */
	public function type(): string;
}
