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
	 * @var string
	 */
	private $cache_group = 'mlp';

	/**
	 * @var string
	 */
	private $cache_key = 'mlp_alternative_language_titles';

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
			$site_id = (int) get_current_blog_id();
		}

		$titles = wp_cache_get( $this->cache_key, $this->cache_group );
		if ( ! is_array( $titles ) ) {
			$titles = [];
		} elseif ( isset( $titles[ $site_id ] ) ) {
			return $titles[ $site_id ];
		}

		$title = $this->site_settings_repository->get_alternative_language_title();
		if ( ! $title ) {
			return '';
		}

		$titles[ $site_id ] = $title;

		wp_cache_set( $this->cache_key, $titles, $this->cache_group );

		return $title;
	}

	/**
	 * Updates the cache entry for the alternative language title of the updated site.
	 *
	 * @since   3.0.0
	 * @wp-hook mlp_blogs_save_fields
	 *
	 * @return bool Whether or not any alternative language titles were updated.
	 */
	public function update(): bool {

		$site_id = (int) ( $_REQUEST['id'] ?? get_current_blog_id() );
		if ( 1 > $site_id ) {
			return false;
		}

		$titles = wp_cache_get( $this->cache_key, $this->cache_group );
		if ( ! isset( $titles[ $site_id ] ) ) {
			return false;
		}

		unset( $titles[ $site_id ] );

		wp_cache_set( $this->cache_key, $titles, $this->cache_group );

		return true;
	}
}
