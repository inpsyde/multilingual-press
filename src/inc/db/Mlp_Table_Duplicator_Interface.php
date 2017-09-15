<?php # -*- coding: utf-8 -*-
/**
 * ${CARET}
 *
 * @version 2014.08.25
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

/**
 * Mlp_Table_Duplicator
 *
 * @version 2014.08.25
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Table_Duplicator_Interface {

	/**
	 * Replace an entire table
	 *
	 * @param  string $new_table
	 * @param  string $old_table
	 * @param  bool   $create Create the new table if it doesn't exists
	 * @return int Number of inserted rows
	 */
	public function replace_content( $new_table, $old_table, $create = false );
}
