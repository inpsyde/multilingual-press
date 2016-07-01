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
	 *
	 * @param   array $params
	 * @param   String $type
	 * @return  array $results
	 */
	public function get_items( array $params = [], $type = OBJECT_K );

	/**
	 * @param array  $items
	 * @param string $field_format
	 * @param string $where_format
	 * @return array
	 */
	public function update_items_by_id( array $items, $field_format = '%s', $where_format = '%d' );

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function insert_item( array $params );

	/**
	 * @return int
	 */
	public function get_page_size();
}
