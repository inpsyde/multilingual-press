<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Interface for all site setting view model implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
interface SiteSettingViewModel {

	/**
	 * Renders the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function render( int $site_id );

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title(): string;
}
