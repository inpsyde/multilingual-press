<?php # -*- coding: utf-8 -*-
/**
 * Interface Mlp_Db_Installer_Interface
 *
 * @version 2014.07.16
 * @author  toscho
 * @license GPL
 */
interface Mlp_Db_Installer_Interface {

	/**
	 * @param Mlp_Db_Schema_Interface $schema
	 * @return void
	 */
	public function install( Mlp_Db_Schema_Interface $schema = null );

	/**
	 * @param Mlp_Db_Schema_Interface $schema
	 * @return false|int
	 */
	public function uninstall( Mlp_Db_Schema_Interface $schema = null );
}
