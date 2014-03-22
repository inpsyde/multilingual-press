<?php # -*- coding: utf-8 -*-
class Mlp_Db_Relationship_Schema implements Mlp_Db_Schema_Interface {

	public function get_table_name() {
		return $GLOBALS['wpdb']->base_prefix . 'multilingual_linked';
	}

	public function get_schema() {
		return array (
			'ml_id'             => 'INT NOT NULL AUTO_INCREMENT',
			'ml_source_blogid'   => 'bigint(20) NOT NULL',
			'ml_source_elementid'    => 'bigint(20) NOT NULL',
			'ml_blogid'    => 'bigint(20) NOT NULL',
			'ml_elementid'    => 'bigint(20) NOT NULL',
			'ml_type'    => 'varchar(20) CHARACTER SET utf8 NOT NULL',
		);
	}

	public function get_primary_key() {
		return 'ml_id';
	}

	public function get_autofilled_keys() {
		return array ( 'ml_id' );
	}

	public function get_index_sql() {
		return 'INDEX ( `ml_blogid` , `ml_elementid` )';
	}

	/**
	 * Not used.
	 * @see Mlp_Db_Schema_Interface::get_default_content()
	 */
	public function get_default_content() {}
}