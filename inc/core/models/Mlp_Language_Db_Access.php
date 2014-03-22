<?php # -*- coding: utf-8 -*-
class Mlp_Language_Db_Access implements Mlp_Data_Access {

	private $page_size = 20;

	private $table_name;
	/**
	 * Constructor.
	 */
	public function __construct( $table_name, $page_size = 20 ) {
		$this->table_name = $GLOBALS['wpdb']->base_prefix . $table_name;
	}

	public function get_total_items_number() {

		global $wpdb;

		$all = $wpdb->get_results(
			"SELECT COUNT(*) as amount FROM `{$this->table_name}`",
			OBJECT_K // Makes the result the first and only key.
		);
		return (int) key( $all );
	}

	public function get_items( $page = 1 ) {

		global $wpdb;

		$limit  = $this->get_limit( $page );
		$query  = "SELECT * FROM `{$this->table_name}` ORDER BY `priority` DESC, `english_name` ASC $limit";
		$result = $wpdb->get_results( $query, OBJECT_K );

		return NULL === $result ? array() : $result;
	}

	public function update_items_by_id( Array $items, $field_format = '%s', $where_format = '%d' ) {

		global $wpdb;

		$queries = array();

		foreach ( $items as $id => $values ) {
			$rows = $wpdb->update(
				$this->table_name,
				(array) $values,
				array( 'ID' => $id ),
				$field_format,
				$where_format
			);
			$queries[ $id ] = $wpdb->last_error;
		}

		return $queries;
	}

	public function get_item( Array $params ) {
	}

	public function insert_item( Array $params ) {
	}

	public function set_page_size( $page_size ) {
		$this->page_size  = $page_size;
	}
	public function get_page_size() {
		return $this->page_size;
	}

	private function get_limit( $page ) {

		if ( -1 === $page )
			return '';

		$start    = $this->page_size * ( $page - 1 );

		return "\nLIMIT $start, $this->page_size";
	}
}