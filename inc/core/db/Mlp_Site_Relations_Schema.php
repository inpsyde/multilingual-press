<?php
/**
 * Schema for site relations.
 *
 * @version 2014.07.09
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Site_Relations_Schema implements Mlp_Db_Schema_Interface {

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
		return $this->wpdb->base_prefix . 'mlp_site_relations';
	}

	/**
	 * Relationship schema.
	 *
	 * See wp_get_db_schema() in wp-admin/includes/schema.php for the default schema.
	 * @return array
	 */
	public function get_schema() {
		return array (
			'ID'     => 'INT NOT NULL AUTO_INCREMENT',
			'site_1' => 'bigint(20) NOT NULL',
			'site_2' => 'bigint(20) NOT NULL'
		);
	}

	/**
	 * @return string
	 */
	public function get_primary_key() {
		return 'ID';
	}

	/**
	 * @return array
	 */
	public function get_autofilled_keys() {
		return array ( 'ID' );
	}

	/**
	 * @return string SQL for INSERT
	 */
	public function get_default_content() {}

	/**
	 * @return string
	 */
	public function get_index_sql() {
		return 'INDEX ( `site_1`, `site_2` ),
		UNIQUE KEY `site_combinations` ( `site_1`, `site_2` )'; // prevent duplicates
	}
}