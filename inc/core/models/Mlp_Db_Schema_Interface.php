<?php # -*- coding: utf-8 -*-
interface Mlp_Db_Schema_Interface {

	public function get_table_name();

	public function get_schema();

	public function get_primary_key();

	public function get_autofilled_keys();

	public function get_default_content();

	public function get_index_sql();
}