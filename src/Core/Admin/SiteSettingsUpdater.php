<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

/**
 * Site settings updater.
 *
 * @package Inpsyde\MultilingualPress\Core\Admin
 * @since   3.0.0
 */
class SiteSettingsUpdater {

	/**
	 * Action hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_DEFINE_INITIAL_SETTINGS = 'multilingualpress.define_initial_site_settings';

	/**
	 * Action hook.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_UPDATE_SETTINGS = 'multilingualpress.update_site_settings';

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
	 * @return void
	 */
	public function define_initial_settings( int $site_id ) {

		$this->update_wplang( $site_id );

		$this->update_language( $site_id );

		$this->update_alternative_language_title( $site_id );

		$this->update_flag_image_url( $site_id );

		$this->update_relationships( $site_id );

		/**
		 * Fires right after the initial settings of a new site have been defined.
		 *
		 * @since 3.0.0
		 *
		 * @param int $site_id Site ID.
		 */
		do_action( self::ACTION_DEFINE_INITIAL_SETTINGS, (int) $site_id );
	}

	/**
	 * Updates the settings of an existing site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	public function update_settings( int $site_id ) {

		$this->update_language( $site_id );

		$this->update_alternative_language_title( $site_id );

		$this->update_flag_image_url( $site_id );

		$this->update_relationships( $site_id );

		/**
		 * Fires right after the initial settings of an existing site have been updated.
		 *
		 * @since 3.0.0
		 *
		 * @param int $site_id Site ID.
		 */
		do_action( self::ACTION_UPDATE_SETTINGS, (int) $site_id );
	}

	/**
	 * Returns the language value from the request.
	 *
	 * @return string Language.
	 */
	private function get_language(): string {

		if (
			empty( $_POST['blog'][ SiteSettingsRepository::NAME_LANGUAGE ] )
			|| ! is_string( $_POST['blog'][ SiteSettingsRepository::NAME_LANGUAGE ] )
			|| '-1' === $_POST['blog'][ SiteSettingsRepository::NAME_LANGUAGE ]
		) {
			return '';
		}

		return $_POST['blog'][ SiteSettingsRepository::NAME_LANGUAGE ];
	}

	/**
	 * Updates the alternative language title for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_alternative_language_title( int $site_id ) {

		$title = empty( $_POST[ SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE ] )
			? ''
			: (string) $_POST[ SiteSettingsRepository::NAME_ALTERNATIVE_LANGUAGE_TITLE ];

		$this->repository->set_alternative_language_title( $title, $site_id );
	}

	/**
	 * Updates the flag image URL for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_flag_image_url( int $site_id ) {

		$url = empty( $_POST[ SiteSettingsRepository::NAME_FLAG_IMAGE_URL ] )
			? ''
			: (string) $_POST[ SiteSettingsRepository::NAME_FLAG_IMAGE_URL ];

		$this->repository->set_flag_image_url( $url, $site_id );
	}

	/**
	 * Updates the language for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_language( int $site_id ) {

		$this->repository->set_language( $this->get_language(), $site_id );
	}

	/**
	 * Updates the relationships for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_relationships( int $site_id ) {

		$relationships = empty( $_POST[ SiteSettingsRepository::NAME_RELATIONSHIPS ] )
			? []
			: array_map( 'intval', (array) $_POST[ SiteSettingsRepository::NAME_RELATIONSHIPS ] );

		$this->repository->set_relationships( $relationships, $site_id );
	}

	/**
	 * Updates the WordPress language for the site with the given ID according to the data in the request.
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return void
	 */
	private function update_wplang( int $site_id ) {

		$language = $this->get_language();
		if ( ! $language ) {
			return;
		}

		$language = reset( $this->languages->get_languages( [
			'fields'     => LanguagesTable::COLUMN_LOCALE,
			'conditions' => [
				[
					'field' => LanguagesTable::COLUMN_HTTP_CODE,
					'value' => str_replace( '_', '-', $language ),
				],
			],
		] ) );
		if ( ! $language ) {
			return;
		}

		$wplang = $language[ LanguagesTable::COLUMN_LOCALE ];

		if ( in_array( $wplang, get_available_languages(), true ) ) {
			update_blog_option( $site_id, 'WPLANG', $wplang );
		}
	}
}
