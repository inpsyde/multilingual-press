<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Data_Access
 *
 * @version 2014.07.16
 * @author  toscho
 * @license GPL
 */
interface Mlp_Data_Access {

	/**
	 * TODO: Get rid of this as soon as the Language Manager has been refactored into a list table.
	 *
	 * @return int
	 */
	public function get_total_items_number();
}
