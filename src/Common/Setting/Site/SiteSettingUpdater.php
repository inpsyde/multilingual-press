<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Interface for all site setting updater implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
interface SiteSettingUpdater {

	/**
	 * Updates the setting with the given data for the site with the given ID.
	 *
	 * @since   3.0.0
	 * @todo    Adapt hook as soon as it is a class constant (see Mlp_Network_Site_Settings_Controller).
	 * @wp-hook mlp_blogs_save_fields
	 *
	 * @param array $data    Data to be saved.
	 * @param int   $site_id Site ID.
	 *
	 * @return bool Whether or not the site setting was updated successfully.
	 */
	public function update( array $data, $site_id );
}
