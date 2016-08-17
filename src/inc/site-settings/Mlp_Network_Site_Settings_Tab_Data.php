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

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Mlp_Network_Site_Settings_Tab_Data::get_action}.
	 *
	 * @return string
	 */
	public function get_action_name() {

		// TODO: Adapt the following as soon as the class got refactored.

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\Mlp_Network_Site_Settings_Tab_Data::get_action'
		);

		return $this->get_action();
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Mlp_Network_Site_Settings_Tab_Data::get_url}.
	 *
	 * @return string
	 */
	public function get_form_action() {

		// TODO: Adapt the following as soon as the class got refactored.

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'(string) Inpsyde\MultilingualPress\Common\Type\Mlp_Network_Site_Settings_Tab_Data::get_url'
		);

		return (string) $this->get_url();
	}

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Mlp_Network_Site_Settings_Tab_Data::get_action}.
	 *
	 * @return string
	 */
	public function get_nonce_action() {

		// TODO: Adapt the following as soon as the class got refactored.

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\Mlp_Network_Site_Settings_Tab_Data::get_action'
		);

		return $this->get_action();
	}
}
