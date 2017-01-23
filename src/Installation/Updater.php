<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\TableInstaller;
use wpdb;

/**
 * Updates any installed plugin data to the current version.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class Updater {

	/**
	 * @var Table
	 */
	private $content_relations_table;

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var Table
	 */
	private $languages_table;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var Table
	 */
	private $site_relations_table;

	/**
	 * @var SiteSettingsRepository
	 */
	private $site_settings_repository;

	/**
	 * @var TableInstaller
	 */
	private $table_installer;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param wpdb                   $db                       WordPress database object.
	 * @param SiteSettingsRepository $site_settings_repository Site settings repository object.
	 * @param TableInstaller         $table_installer          Table installer object.
	 * @param Table                  $content_relations_table  Content relations table object.
	 * @param Table                  $languages_table          Languages table object.
	 * @param Table                  $site_relations_table     Site relations table object.
	 * @param SiteRelations          $site_relations           Site relations API.
	 */
	public function __construct(
		wpdb $db,
		SiteSettingsRepository $site_settings_repository,
		TableInstaller $table_installer,
		Table $content_relations_table,
		Table $languages_table,
		Table $site_relations_table,
		SiteRelations $site_relations
	) {

		$this->db = $db;

		$this->site_settings_repository = $site_settings_repository;

		$this->table_installer = $table_installer;

		$this->content_relations_table = $content_relations_table;

		$this->languages_table = $languages_table;

		$this->site_relations_table = $site_relations_table;

		$this->site_relations = $site_relations;
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

		if ( VersionNumber::FALLBACK_VERSION === $installed_version ) {
			// TODO: Move either to separate class or method on an existing class in the Language API namespace.
			$this->import_active_languages();
		}

		if ( version_compare( $installed_version, '2.0.4', '<' ) ) {
			$this->table_installer->install( $this->site_relations_table );

			$this->import_site_relations();

			if ( version_compare( $installed_version, '2.3.2', '<' ) ) {
				$this->update_type_column();
			}
		}

		// Remove obsolete plugin data.
		delete_option( 'inpsyde_companyname' );
	}

	/**
	 * Moves site relations from deprecated site options to the new custom network table.
	 *
	 * @return void
	 */
	private function import_site_relations() {

		// TODO: With WordPress 4.6 + 2, just use `get_sites()`, and remove `$is_pre_4_6`.

		$is_pre_4_6 = version_compare( $GLOBALS['wp_version'], '4.6-RC1', '<' );

		$all_sites = $is_pre_4_6 ? wp_get_sites() : get_sites();
		foreach ( $all_sites as $site ) {
			// TODO: With WordPress 4.6 + 2, just use `$site->id`.
			$site_id = $is_pre_4_6 ? $site['blog_id'] : $site->id;

			$linked = get_blog_option( $site_id, 'inpsyde_multilingual_blog_relationship', [] );
			if ( $linked ) {
				$this->site_relations->insert_relations( $site_id, $linked );
			}

			delete_blog_option( $site_id, 'inpsyde_multilingual_blog_relationship' );
		}
	}

	/**
	 * Updates invalid type field entries in the content relations table.
	 *
	 * @return void
	 */
	private function update_type_column() {

		$table = $this->content_relations_table->name();

		$this->db->query( "UPDATE $table set ml_type = 'post' WHERE ml_type NOT IN('post','term')" );
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

		$query = "SELECT ID FROM $table WHERE wp_locale = %s OR iso_639_1 = %s";

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
