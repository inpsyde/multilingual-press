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

		if ( ! $site_id ) {
			$site_id = get_current_blog_id();
		}

		/*
		 * @TODO There was cache here, now removed. Think about adding it again.
		 * cache key was 'mlp_alternative_language_titles' and cache group 'mlp'.
		 * Cache was updated with in a `update()` method, nw deleted, hooked into 'mlp_blogs_save_fields'.
		 * Hook was added in AlternativeLanguageTitleInAdminBar\ServiceProvider::bootstrap()
		 */

		if ( isset( $titles[ $site_id ] ) ) {
			return (string) $titles[ $site_id ];
		}

		$title = $this->site_settings_repository->get_alternative_language_title();
		if ( ! $title ) {
			return '';
		}

		$titles[ $site_id ] = $title;

		return $title;
	}
}
