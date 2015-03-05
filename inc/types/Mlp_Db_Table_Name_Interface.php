<?php # -*- coding: utf-8 -*-
/**
 * Provide a validated table name
 *
 * @version 2015.01.08
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Db_Table_Name_Interface {

	/**
	 * Get the table name as string.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Does the table exists already?
	 *
	 * @return bool
	 */
	public function exists();
}