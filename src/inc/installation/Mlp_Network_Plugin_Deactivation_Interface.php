<?php # -*- coding: utf-8 -*-
/**
 * ${CARET}
 *
 * @version 2014.09.08
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

/**
 * ${CARET}
 *
 * @version 2014.09.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Network_Plugin_Deactivation_Interface {

	/**
	 * Searches in active network plugins.
	 *
	 * It will find even partial matches, so you can pass a directory name, and
	 * it will find the files in that directory. The search is case-sensitive.
	 *
	 * @param  array $plugins List of plugin base names. See plugin_basename().
	 * @return array           All matches that were removed.
	 */
	public function deactivate( array $plugins );
}
