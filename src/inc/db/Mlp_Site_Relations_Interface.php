<?php # -*- coding: utf-8 -*-
/**
 * Handle relationships between sites (blogs) in a network.
 *
 * @version 2014.07.13
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Site_Relations_Interface {

	/**
	 * Fetch related sites.
	 *
	 * @param  int  $site_id
	 * @return array
	 */
	public function get_related_sites( $site_id = 0 );

	/**
	 * Delete relationships.
	 *
	 * @param int $site_1
	 * @param int $site_2 Optional. If left out, all relations will be deleted.
	 * @return int
	 */
	public function delete_relation( $site_1, $site_2 = 0 );

	/**
	 * Create new relation for one site with one or more others.
	 *
	 * @param int       $site_1
	 * @param int|array $sites ID or array of IDs
	 * @return int Number of affected rows.
	 */
	public function set_relation( $site_1, $sites );
}
