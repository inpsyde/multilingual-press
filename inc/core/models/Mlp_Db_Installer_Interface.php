<?php # -*- coding: utf-8 -*-
interface Mlp_Db_Installer_Interface {

	public function __construct( Mlp_Db_Schema_Interface $db_info );

	public function install( Mlp_Db_Schema_Interface $schema = NULL );

	public function uninstall( Mlp_Db_Schema_Interface $schema = NULL );
}