<?php # -*- coding: utf-8 -*-

// TODO: Refactor as soon as the Language Manager namespace has been discussed. If we use a ListTable, this is obsolete.

/**
 * Provide information for paginated views.
 *
 * Instances are usually passes to views.
 *
 * @author  Inpsyde GmbH, MarketPress, toscho
 * @version 2013.12.22
 * @package MultilingualPress\Pagination\Models
 * @uses    Mlp_Data_Access
 */
interface Mlp_Browsable {

	/**
	 * Amount of items.
	 *
	 * @return integer
	 */
	public function get_total_items();

	/**
	 * Amount of pages.
	 *
	 * @return integer
	 */
	public function get_total_pages();

	/**
	 * Amount of items per page.
	 *
	 * @return integer
	 */
	public function get_items_per_page();

	/**
	 * Current page number.
	 *
	 * @return integer
	 */
	public function get_current_page();
}