<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\TableInstaller;

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
	 * @var Table
	 */
	private $languages_table;

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
	 * @param Table                  $languages_table          Languages table object.
	 */
	public function __construct( \wpdb $db, SiteSettingsRepository $site_settings_repository, Table $languages_table ) {

		$this->db = $db;

		$this->site_settings_repository = $site_settings_repository;

		$this->languages_table = $languages_table;
	}

	/**
	 * Updates any installed plugin data to the current version.
	 *
	 * @since 3.0.0
	 *
	 * @param VersionNumber  $installed_version       Installed MultilingualPress version.
	 *
	 * @return void
	 */
	public function update( VersionNumber $installed_version ) {

		if ( VersionNumber::FALLBACK_VERSION === (string) $installed_version ) {
			// TODO: Move either to separate class or method on an existing class in the Language API namespace.
			// TODO: Check if this is needed exactly like this (or similar and compatible) in the language manager.
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

		$table = $this->languages_table->name();

		$query = "SELECT ID FROM {$table} WHERE wp_locale = %s OR iso_639_1 = %s";

		array_walk( $languages, function ( array $language ) use ( $table, $query ) {

			if ( ! empty( $language['lang'] ) ) {
				$language_id = $this->db->get_var( $this->db->prepare( $query, $language['lang'], $language['lang'] ) );
				if ( $language_id ) {
					$this->db->update(
						$table,
						[ 'priority' => 10 ],
						[ 'ID' => $language_id ]
					);

					return;
				}
			} else {
				$language['lang'] = '';
			}

			if ( ! isset( $language['text'] ) ) {
				$language['text'] = '';
			}

			$this->db->insert( $table, [
				'english_name' => '' === $language['text'] ? $language['lang'] : $language['text'],
				'wp_locale'    => $language['lang'],
				'http_name'    => str_replace( '_', '-', $language['lang'] ),
			] );
		} );
	}
}
