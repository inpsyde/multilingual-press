<?php # -*- coding: utf-8 -*-
// might be just a property list
interface Mlp_Options_Page_Data {

	public function get_title();
	public function get_form_action();
	public function get_nonce_action();
	public function get_nonce_name();
	public function get_action_name();
}