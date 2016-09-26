<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

use ArrayAccess;

/**
 * Interface for all plugin properties implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface PluginProperties extends ArrayAccess {

	/**
	 * Returns the base name of the plugin.
	 *
	 * @since 3.0.0
	 *
	 * @return string The base name of the plugin.
	 */
	public function plugin_base_name();

	/**
	 * Returns the absolute path of the plugin root folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The absolute path of the plugin root folder.
	 */
	public function plugin_dir_path();

	/**
	 * Returns the URL of the plugin root folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The URL of the plugin root folder.
	 */
	public function plugin_dir_url();

	/**
	 * Returns the absolute path of main plugin file.
	 *
	 * @since 3.0.0
	 *
	 * @return string The absolute path of main plugin file.
	 */
	public function plugin_file_path();

	/**
	 * Returns the plugin name as given in the plugin headers.
	 *
	 * @since 3.0.0
	 *
	 * @return string The plugin name.
	 */
	public function plugin_name();

	/**
	 * Returns the URL of the plugin website.
	 *
	 * @since 3.0.0
	 *
	 * @return string The URL of the plugin website
	 */
	public function plugin_website();

	/**
	 * Returns the plugin version.
	 *
	 * @since 3.0.0
	 *
	 * @return string The plugin version.
	 */
	public function version();

	/**
	 * Returns the plugin text domain.
	 *
	 * @since 3.0.0
	 *
	 * @return string The plugin text domain.
	 */
	public function text_domain();

	/**
	 * Returns the absolute path of the folder with the plugin translation files.
	 *
	 * @since 3.0.0
	 *
	 * @return string The absolute path of the folder with the plugin translation files.
	 */
	public function text_domain_path();
}
