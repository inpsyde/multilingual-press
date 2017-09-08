<?php

/**
 * Schema for site relations.
 *
 * @version 2015.06.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Site_Relations_Schema implements Mlp_Db_Schema_Interface {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param wpdb $wpdb Database object.
	 */
	public function __construct( wpdb $wpdb ) {

		$this->wpdb = $wpdb;
	}

	/**
	 * Return the table name.
	 *
	 * @return string
	 */
	public function get_table_name() {

		return $this->wpdb->base_prefix . 'mlp_site_relations';
	}

	/**
	 * Return the table schema.
	 *
	 * See wp_get_db_schema() in wp-admin/includes/schema.php for the default schema.
	 *
	 * @return array
	 */
	public function get_schema() {

		return array(
			'ID'     => 'INT NOT NULL AUTO_INCREMENT',
			'site_1' => 'bigint(20) NOT NULL',
			'site_2' => 'bigint(20) NOT NULL',
		);
	}

	/**
	 * Return the primary key.
	 *
	 * @return string
	 */
	public function get_primary_key() {

		return 'ID';
	}

	/**
	 * Return the array of autofilled keys.
	 *
	 * @return array
	 */
	public function get_autofilled_keys() {

		return array(
			'ID',
		);
	}

	/**
	 * Return the SQL string for any indexes and unique keys.
	 *
	 * @return string
	 */
	public function get_index_sql() {

		// Due to dbDelta: KEY (not INDEX), and no spaces inside brackets!
		return 'UNIQUE KEY site_combinations (site_1,site_2)';
	}

	/**
	 * Return the SQL string for any default content.
	 *
	 * @return string
	 */
	public function get_default_content() {

		return '';
	}

}
