<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\Setting;

_deprecated_file(
	'Mlp_Options_Page_Data',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\Type\Setting'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see Setting}.
 */
interface Mlp_Options_Page_Data extends Setting {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Setting::get_action}.
	 *
	 * @return string
	 */
	public function get_action_name();

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Setting::get_url}.
	 *
	 * @return string
	 */
	public function get_form_action();

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Setting::get_action}.
	 *
	 * @return string
	 */
	public function get_nonce_action();
}
