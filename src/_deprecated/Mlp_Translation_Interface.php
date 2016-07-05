<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\Translation;

_deprecated_file(
	'Mlp_Translation_Interface',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\Type\Translation'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see Translation}.
 */
interface Mlp_Translation_Interface extends Translation {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Translation::get_type}.
	 *
	 * @return string
	 */
	public function get_page_type();

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Translation::get_remote_title}.
	 *
	 * @return string
	 */
	public function get_target_title();
}
