<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\EscapedURL;
use Inpsyde\MultilingualPress\Common\Type\Setting;
use Inpsyde\MultilingualPress\Common\Type\URL;

/**
 * Class Mlp_Network_Site_Settings_Tab_Data
 *
 * @version 2015.06.30
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Network_Site_Settings_Tab_Data implements Setting {

	/**
	 * Returns the action name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Action name.
	 */
	public function get_action() {

		return 'mlp_network_site_settings';
	}

	/**
	 * Returns the nonce name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce name.
	 */
	public function get_nonce_name() {

		return 'mlp_network_site_settings_nonce';
	}

	/**
	 * Returns the title of the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Setting title.
	 */
	public function get_title() {

		return '';
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
