<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Setting\Site;

/**
 * Interface for all site settings section view model implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Setting\Site
 * @since   3.0.0
 */
interface SiteSettingsSectionViewModel {

	/**
	 * Returns the ID of the site settings section.
	 *
	 * @since 3.0.0
	 *
	 * @return string The ID for the site settings section.
	 */
	public function id(): string;

	/**
	 * Returns the markup for the site settings section.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the site setting markup was rendered successfully.
	 */
	public function render_view( int $site_id ): bool;

	/**
	 * Returns the title of the site settings section.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site settings section.
	 */
	public function title(): string;
}
