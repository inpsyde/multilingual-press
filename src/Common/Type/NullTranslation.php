<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Null translation implementation.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
final class NullTranslation implements Translation {

	/**
	 * Returns the icon URL object.
	 *
	 * @since 3.0.0
	 *
	 * @return URL Icon URL object.
	 */
	public function icon_url() {

		return new EscapedURL( '' );
	}

	/**
	 * Returns the language object.
	 *
	 * @since 3.0.0
	 *
	 * @return Language Language object.
	 */
	public function language() {

		return new NullLanguage();
	}

	/**
	 * Returns the remote title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote title.
	 */
	public function remote_title() {

		return '';
	}

	/**
	 * Returns the remote URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Remote URL.
	 */
	public function remote_url() {

		return '';
	}

	/**
	 * Returns the source site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Source site ID.
	 */
	public function source_site_id() {

		return 0;
	}

	/**
	 * Returns the target content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target content ID.
	 */
	public function target_content_id() {

		return 0;
	}

	/**
	 * Returns the target site ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Target site ID.
	 */
	public function target_site_id() {

		return 0;
	}

	/**
	 * Returns the content type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Content type.
	 */
	public function type() {

		return '';
	}
}
