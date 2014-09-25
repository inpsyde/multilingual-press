<?php # -*- coding: utf-8 -*-
/**
 * Relationships between content blocks (posts, terms, whatever).
 *
 * @version 2014.07.08
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Content_Relations_Schema implements Mlp_Db_Schema_Interface {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @param wpdb $wpdb
	 */
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @return string
	 */
	public function get_table_name() {
		return $this->wpdb->base_prefix . 'multilingual_linked';
	}

	/**
	 * @return array
	 */
	public function get_schema() {
		return array (
			'ml_id'               => 'INT NOT NULL AUTO_INCREMENT',
			'ml_source_blogid'    => 'bigint(20) NOT NULL',
			'ml_source_elementid' => 'bigint(20) NOT NULL',
			'ml_blogid'           => 'bigint(20) NOT NULL',
			'ml_elementid'        => 'bigint(20) NOT NULL',
			'ml_type'             => 'varchar(20) CHARACTER SET utf8 NOT NULL',
		);
	}

	/**
	 * @return string
	 */
	public function get_primary_key() {
		return 'ml_id';
	}

	/**
	 * @return array
	 */
	public function get_autofilled_keys() {
		return array ( 'ml_id' );
	}

	/**
	 * @return string
	 */
	public function get_index_sql() {
		return 'INDEX ( `ml_blogid` , `ml_elementid` )';
	}

	/**
	 * Not used.
	 * @see Mlp_Db_Schema_Interface::get_default_content()
	 */
	public function get_default_content() {}
}