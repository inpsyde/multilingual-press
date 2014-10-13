<?php # -*- coding: utf-8 -*-
/**
 * Manage paths and URLs
 *
 * @version 2014.10.09
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Locations_Interface {

	/**
	 * @param  string $identifier
	 * @param  string $type
	 * @return string
	 */
	public function get_dir( $identifier, $type );

	/**
	 * @param  string $path
	 * @param  string $url
	 * @param  string $identifier
	 * @return void
	 */
	public function add_dir( $path, $url, $identifier = '' );

	/**
	 * Check if a directory type is registered
	 *
	 * @param  string $identifier
	 * @return bool
	 */
	public function has_dir( $identifier );
}