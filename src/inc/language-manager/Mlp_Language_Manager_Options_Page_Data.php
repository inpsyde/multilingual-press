<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Language_Manager_Options_Page_Data
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Manager_Options_Page_Data implements Mlp_Options_Page_Data {

	/**
	 * @var string
	 */
	private $page_title;

	/**
	 * @param string $page_title
	 */
	public function __construct( $page_title ) {
		$this->page_title = $page_title;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->page_title;
	}

	/**
	 * @return string
	 */
	public function get_form_action() {
		return admin_url( 'admin-post.php' );
	}

	/**
	 * @return string
	 */
	public function get_nonce_action() {
		return 'mlp_update_languages';
	}

	/**
	 * @return string
	 */
	public function get_nonce_name() {
		return 'mlp_language_table_nonce';
	}

	/**
	 * @return string
	 */
	public function get_action_name() {
		return 'mlp_update_languages';
	}
}
