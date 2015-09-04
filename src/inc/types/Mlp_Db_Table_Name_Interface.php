<?php # -*- coding: utf-8 -*-
/**
 * Provide a table name.
 *
 * @version 2015.08.31
 * @author  Inpsyde GmbH, toscho, tf
 * @license GPL
 */
interface Mlp_Db_Table_Name_Interface {

	/**
	 * Return the table name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Check whether or not the table already exists.
	 *
	 * @return bool
	 */
	public function exists();

}
