<?php # -*- coding: utf-8 -*-

/**
 * Relationships between content blocks (posts, terms, whatever).
 *
 * @version 2015.06.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Content_Relations_Schema implements Mlp_Db_Schema_Interface {

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

		return $this->wpdb->base_prefix . 'multilingual_linked';
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
			'ml_id'               => 'INT NOT NULL AUTO_INCREMENT',
			'ml_source_blogid'    => 'bigint(20) NOT NULL',
			'ml_source_elementid' => 'bigint(20) NOT NULL',
			'ml_blogid'           => 'bigint(20) NOT NULL',
			'ml_elementid'        => 'bigint(20) NOT NULL',
			'ml_type'             => 'varchar(20) CHARACTER SET utf8 NOT NULL',
		);
	}

	/**
	 * Return the primary key.
	 *
	 * @return string
	 */
	public function get_primary_key() {

		return 'ml_id';
	}

	/**
	 * Return the array of autofilled keys.
	 *
	 * @return array
	 */
	public function get_autofilled_keys() {

		return array(
			'ml_id',
		);
	}

	/**
	 * Return the SQL string for any indexes and unique keys.
	 *
	 * @return string
	 */
	public function get_index_sql() {

		// Due to dbDelta: KEY (not INDEX), and no spaces inside brackets!
		return 'KEY blog_element (ml_blogid,ml_elementid)';
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
