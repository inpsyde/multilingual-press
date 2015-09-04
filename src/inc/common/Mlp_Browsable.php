<?php # -*- coding: utf-8 -*-
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
	 * Constructor.
	 *
	 * @param Mlp_Data_Access $data
	 */
	public function __construct( Mlp_Data_Access $data );

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