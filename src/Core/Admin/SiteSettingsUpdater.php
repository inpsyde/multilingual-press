<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\Languages;

/**
 * Site settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class SiteSettingsUpdater {

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var SiteSettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param SiteSettingsRepository $repository Site settings repository object.
	 * @param Languages              $languages  Languages API object.
	 */
	public function __construct( SiteSettingsRepository $repository, Languages $languages ) {

		$this->repository = $repository;

		$this->languages = $languages;
	}

	/**
	 * Defines the initial settings of a new site.
	 *
	 * @since   3.0.0
	 * @wp-hook wpmu_new_blog
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the initial settings were defined successfully.
	 */
	public function define_initial_settings( $site_id ) {

		$success = true;

		$success = $success && $this->update_wplang( $site_id );

		$success = $success && $this->update_language( $site_id );

		$success = $success && $this->update_alternative_language_title( $site_id );

		$success = $success && $this->update_flag_image_url( $site_id );

		$success = $success && $this->update_relationships( $site_id );

		return $success;
	}

	/**
	 * Returns the language value from the request.
	 *
	 * @return string Language.
	 */
	private function get_language() {

		if (
			empty( $_POST[ SiteSettingsRepository::NAME_LANGUAGE ] )
			|| ! is_string( $_POST[ SiteSettingsRepository::NAME_LANGUAGE ] )
			|| '-1' === $_POST[ SiteSettingsRepository::NAME_LANGUAGE ]
		) {
			return '';
		}

		return $_POST[ SiteSettingsRepository::NAME_LANGUAGE ];
	}

	/**
	 * Updates the alternative language title for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the alternative language title was updated successfully.
	 */
	private function update_alternative_language_title( $site_id ) {

		$title = empty( $_POST[ SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE ] )
			? ''
			: (string) $_POST[ SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE ];

		return $this->repository->set_alternative_language_title( $title, $site_id );
	}

	/**
	 * Updates the flag image URL for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the flag image URL was updated successfully.
	 */
	private function update_flag_image_url( $site_id ) {

		$url = empty( $_POST[ SiteSettingsRepository::NAME_FLAG_IMAGE_URL ] )
			? ''
			: (string) $_POST[ SiteSettingsRepository::NAME_FLAG_IMAGE_URL ];

		return $this->repository->set_flag_image_url( $url, $site_id );
	}

	/**
	 * Updates the language for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the language was updated successfully.
	 */
	private function update_language( $site_id ) {

		return $this->repository->set_language( $this->get_language(), $site_id );
	}

	/**
	 * Updates the relationships for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the relationships were updated successfully.
	 */
	private function update_relationships( $site_id ) {

		$relationships = empty( $_POST[ SiteSettingsRepository::NAME_RELATIONSHIPS ] )
			? []
			: array_map( 'intval', (array) $_POST[ SiteSettingsRepository::NAME_RELATIONSHIPS ] );

		return $this->repository->set_relationships( $relationships, $site_id );
	}

	/**
	 * Updates the WordPress language for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return bool Whether or not the WordPress language was updated successfully.
	 */
	private function update_wplang( $site_id ) {

		$language = $this->get_language();
		if ( ! $language ) {
			return true;
		}

		$language = reset( $this->languages->get_languages( [
			'fields'     => 'wp_locale',
			'conditions' => [
				[
					'field' => 'http_name',
					'value' => str_replace( '_', '-', $language ),
				],
			],
		] ) );
		if ( ! $language ) {
			return true;
		}

		$wplang = $language->wp_locale;

		if ( in_array( $wplang, get_available_languages(), true ) ) {
			return update_blog_option( $site_id, 'WPLANG', $wplang );
		}

		return true;
	}
}
