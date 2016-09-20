<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Factory\TypeFactory;
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
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * @param TypeFactory $type_factory Type factory object.
	 */
	public function __construct( TypeFactory $type_factory ) {

		$this->type_factory = $type_factory;
	}

	/**
	 * Returns the action name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Action name.
	 */
	public function action() {

		return 'mlp_network_site_settings';
	}

	/**
	 * Returns the nonce name for the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce name.
	 */
	public function nonce_name() {

		return 'mlp_network_site_settings_nonce';
	}

	/**
	 * Returns the title of the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string Setting title.
	 */
	public function title() {

		return '';
	}

	/**
	 * Returns the URL to be used in the according form.
	 *
	 * @since 3.0.0
	 *
	 * @return URL URL to submit updates to.
	 */
	public function url() {

		return $this->type_factory->create_url( [
			admin_url( 'admin-post.php' ),
		] );
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
			'Mlp_Network_Site_Settings_Tab_Data::get_action'
		);

		return $this->action();
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
			'(string) Mlp_Network_Site_Settings_Tab_Data::get_url'
		);

		return (string) $this->url();
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
			'Mlp_Network_Site_Settings_Tab_Data::get_action'
		);

		return $this->action();
	}
}
