<?php # -*- coding: utf-8 -*-

class Mlp_Language_Manager_Options_Page_Data implements Mlp_Options_Page_Data {

	private $page_title;
	/**
	 * Constructor.
	 */
	public function __construct( $page_title ) {
		$this->page_title = $page_title;
	}
	public function get_title() {
		return $this->page_title;
	}
	public function get_form_action() {
		return admin_url( 'admin-post.php' );
	}
	public function get_nonce_action() {
		return 'mlp_update_languages';
	}
	public function get_nonce_name() {
		return 'mlp_language_table_nonce';
	}
	public function get_action_name() {
		return 'mlp_update_languages';
	}
}
