<?php # -*- coding: utf-8 -*-

/**
 * Interface for SQL tables
 *
 * @version 2015.06.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Db_Schema_Interface {

	/**
	 * Return the table name.
	 *
	 * @return string
	 */
	public function get_table_name();

	/**
	 * Return the table schema.
	 *
	 * See wp_get_db_schema() in wp-admin/includes/schema.php for the default schema.
	 *
	 * @return array
	 */
	public function get_schema();

	/**
	 * Return the primary key.
	 *
	 * @return string
	 */
	public function get_primary_key();

	/**
	 * Return the array of autofilled keys.
	 *
	 * @return array
	 */
	public function get_autofilled_keys();

	/**
	 * Return the SQL string for any indexes and unique keys.
	 *
	 * @return string
	 */
	public function get_index_sql();

	/**
	 * Return the SQL string for any default content.
	 *
	 * @return string
	 */
	public function get_default_content();

}
