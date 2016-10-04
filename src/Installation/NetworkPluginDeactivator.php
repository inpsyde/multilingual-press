<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

/**
 * Deactivates plugins network-wide.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
interface NetworkPluginDeactivator {

	/**
	 * Name of the option that WordPress uses to store all plugins activated network-wide.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION = 'active_sitewide_plugins';

	/**
	 * Deactivates the given plugins network-wide.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $plugins Plugin base names.
	 *
	 * @return string[] An array with all plugins that were deactivated.
	 */
	public function deactivate_plugins( array $plugins );
}
