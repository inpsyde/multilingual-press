<?php # -*- coding: utf-8 -*-
/**
 * Interface for SQL tables
 *
 * @version 2014.07.08
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Db_Schema_Interface {

	/**
	 * @return string
	 */
	public function get_table_name();

	/**
	 * @return array
	 */
	public function get_schema();

	/**
	 * @return string
	 */
	public function get_primary_key();

	/**
	 * @return array
	 */
	public function get_autofilled_keys();

	/**
	 * @return string SQL for INSERT
	 */
	public function get_default_content();

	/**
	 * @return string
	 */
	public function get_index_sql();
}