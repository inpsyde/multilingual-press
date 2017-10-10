<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\API\Languages;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;

/**
 * Updates any installed plugin data to the current version.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class Updater {

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var Languages
	 */
	private $languages;

	/**
	 * @var SiteSettingsRepository
	 */
	private $site_settings_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb                  $db                       WordPress database object.
	 * @param SiteSettingsRepository $site_settings_repository Site settings repository object.
	 * @param Languages              $languages                Languages API object.
	 */
	public function __construct( \wpdb $db, SiteSettingsRepository $site_settings_repository, Languages $languages ) {

		$this->db = $db;

		$this->site_settings_repository = $site_settings_repository;

		$this->languages = $languages;
	}

	/**
	 * Updates any installed plugin data to the current version.
	 *
	 * @since 3.0.0
	 *
	 * @param VersionNumber $installed_version Installed MultilingualPress version.
	 *
	 * @return void
	 */
	public function update( VersionNumber $installed_version ) {

		if ( VersionNumber::FALLBACK_VERSION === (string) $installed_version ) {
			$this->import_active_languages();
		}
	}

	/**
	 * Imports all active languages from the according network option into the languages table.
	 *
	 * @return void
	 */
	private function import_active_languages() {

		$languages = $this->site_settings_repository->get_settings();
		if ( ! $languages ) {
			return;
		}

		$languages = array_filter( (array) $languages, 'is_array' );
		if ( ! $languages ) {
			return;
		}

		$languages = array_map( function ( array $language ) {

			$locale = (string) ( $language[ SiteSettingsRepository::KEY_LANGUAGE ] ?? '' );

			// TODO: Check why the English name (!) is set to the custom name or the locale!
			$name = (string) ( $language[ SiteSettingsRepository::KEY_ALTERNATIVE_LANGUAGE_TITLE ] ?? $locale );

			return [
				LanguagesTable::COLUMN_ENGLISH_NAME => $name,
				LanguagesTable::COLUMN_LOCALE       => $locale,
				LanguagesTable::COLUMN_HTTP_CODE    => str_replace( '_', '-', $locale ),
			];
		}, $languages );

		array_walk( $languages, [ $this->languages, 'import_language' ] );
	}
}
