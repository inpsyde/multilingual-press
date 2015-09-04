<?php # -*- coding: utf-8 -*-

/**
 * Handle scripts and stylesheets
 *
 * @version 2015.07.06
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
interface Mlp_Assets_Interface {

	/**
	 * Add an asset.
	 *
	 * @param string $handle       Unique handle.
	 * @param string $file         File.
	 * @param array  $dependencies Optional. Dependencies. Defaults to array().
	 * @param array  $l10n         Optional. Localized data. Defaults to array().
	 *
	 * @return bool
	 */
	public function add( $handle, $file, $dependencies = array(), $l10n = array() );

	/**
	 * Provide assets for the given handles.
	 *
	 * @param array|string $handles One or more asset handles.
	 *
	 * @return bool
	 */
	public function provide( $handles );

	/**
	 * Register the assets.
	 *
	 * @wp-hook wp_loaded
	 *
	 * @return void
	 */
	public function register();

}
