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
	 * Deactivates the given plugins network-wide.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $plugins Plugin base names.
	 *
	 * @return string[] An array with all plugins that were deactivated.
	 */
	public function deactivate_plugins( array $plugins ): array;
}
