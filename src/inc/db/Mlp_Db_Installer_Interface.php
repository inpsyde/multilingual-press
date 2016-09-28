<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Database\Table;

/**
 * Interface Mlp_Db_Installer_Interface
 *
 * @version 2014.07.16
 * @author  toscho
 * @license GPL
 */
interface Mlp_Db_Installer_Interface {

	/**
	 * @param Table $schema
	 * @return void
	 */
	public function install( Table $schema = NULL );

	/**
	 * @param Table $schema
	 * @return FALSE|int
	 */
	public function uninstall( Table $schema = NULL );
}
