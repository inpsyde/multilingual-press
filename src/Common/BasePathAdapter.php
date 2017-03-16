<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Interface for all base path adapter implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface BasePathAdapter {

	/**
	 * Returns the correct basedir path of the current site's uploads folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The correct basedir path of the current site's uploads folder.
	 */
	public function basedir(): string;

	/**
	 * Returns the correct baseurl path of the current site's uploads folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The correct baseurl path of the current site's uploads folder.
	 */
	public function baseurl(): string;
}
