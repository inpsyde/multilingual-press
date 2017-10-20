<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;

/**
 * Cached access to alternative language titles.
 *
 * @package Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar
 * @since   3.0.0
 */
class AlternativeLanguageTitles {

	/**
	 * @var SiteSettingsRepository
	 */
	private $site_settings_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $site_settings_repository Site settings repository object.
	 */
	public function __construct( SiteSettingsRepository $site_settings_repository ) {

		$this->site_settings_repository = $site_settings_repository;
	}

	/**
	 * Returns the alternative language title for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Optional. Site ID. Defaults to 0.
	 *
	 * @return string The alternative language title for the site with the given ID.
	 */
	public function get( int $site_id = 0 ): string {

		$site_id = $site_id ?: get_current_blog_id();

		return $this->site_settings_repository->get_alternative_language_title( $site_id );
	}
}
