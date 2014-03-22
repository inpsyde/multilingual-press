<?php # -*- coding: utf-8 -*-
interface Mlp_Data_Access {

	public function get_total_items_number();
	public function get_items();
	public function update_items_by_id( Array $items, $field_format = '%s', $where_format = '%d' );
	public function get_item( Array $params );
	public function insert_item( Array $params );
	public function get_page_size();
}