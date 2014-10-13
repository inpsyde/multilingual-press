<?php # -*- coding: utf-8 -*-
/**
 * Handle scripts and stylesheets
 *
 * @version 2014.10.09
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Assets_Interface {

	/**
	 * @param  string $handle
	 * @param  string $file
	 * @param  array  $dependencies
	 * @return bool
	 */
	public function add( $handle, $file, $dependencies = array () );

	/**
	 * @param $handles
	 * @return bool
	 */
	public function provide( $handles );

	/**
	 * @wp-hook wp_loaded
	 * @return void
	 */
	public function register();
}