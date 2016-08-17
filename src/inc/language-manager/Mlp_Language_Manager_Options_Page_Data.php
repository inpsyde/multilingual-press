<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\EscapedURL;
use Inpsyde\MultilingualPress\Common\Type\Setting;
use Inpsyde\MultilingualPress\Common\Type\URL;

/**
 * Class Mlp_Language_Manager_Options_Page_Data
 *
 * @version 2014.07.16
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Manager_Options_Page_Data implements Setting {

	/**
	 * @var string
	 */
	private $page_title;

	/**
	 * @param string $page_title
	 */
	public function __construct( $page_title ) {

		$this->page_title = (string) $page_title;
	}

	/**
	 * Returns the action name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Action name.
	 */
	public function get_action() {

		return 'mlp_update_languages';
	}

	/**
	 * Returns the nonce name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce name.
	 */
	public function get_nonce_name() {

		return 'mlp_language_table_nonce';
	}

	/**
	 * Returns the title of the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Setting title.
	 */
	public function get_title() {

		return $this->page_title;
	}

	/**
	 * Returns the URL to be used in the according form.
	 *
	 * @since 3.0.0
	 *
	 * @return URL URL to submit updates to.
	 */
	public function get_url() {

		return EscapedURL::create( admin_url( 'admin-post.php' ) );
	}
}
