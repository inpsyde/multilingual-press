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
	 * @return int
	 */
	public function get_total_items_number();

	/**
	 * TODO: Move to Languages API class.
	 *
	 * @param   array $params
	 * @param   String $type
	 * @return  array $results
	 */
	public function get_items( array $params = [], $type = OBJECT_K );

	/**
	 * TODO: Move to Languages API class.
	 *
	 * @param array  $items
	 * @param string $field_format
	 * @param string $where_format
	 * @return array
	 */
	public function update_items_by_id( array $items, $field_format = '%s', $where_format = '%d' );

	/**
	 * @return int
	 */
	public function get_page_size();
}
