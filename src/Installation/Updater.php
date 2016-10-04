<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Service\Container;
use wpdb;

/**
 * Updates any installed plugin data to the current version.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class Updater {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 */
	public function __construct( Container $container ) {

		$this->container = $container;

		$this->db = $GLOBALS['wpdb'];
	}

	/**
	 * Updates any installed plugin data to the current version.
	 *
	 * @since 3.0.0
	 *
	 * @param VersionNumber $installed_version Installed MultilingualPress version.
	 * @param VersionNumber $current_version   Current MultilingualPress version.
	 *
	 * @return bool Whether or not the plugin data was updated succesffully.
	 */
	public function update( VersionNumber $installed_version, VersionNumber $current_version ) {

		$this->container['network_plugin_deactivator']->deactivate_plugins( [
			'disable-acf.php',
			'mlp-wp-seo-compat.php',
		] );

		if ( VersionNumber::FALLBACK_VERSION === $installed_version ) {
			// TODO: Move either to separate class or method on an existing class in the Language API namespace.
			$this->import_active_languages();
		}

		if ( version_compare( $installed_version, '2.0.4', '<' ) ) {
			$this->container['multilingualpress.table_installer']->install(
				$this->container['multilingualpress.site_relations_table']
			);

			$this->import_site_relations();

			if ( version_compare( $installed_version, '2.3.2', '<' ) ) {
				$this->update_type_column();
			}
		}

		// Remove obsolete plugin data.
		delete_option( 'inpsyde_companyname' );

		return update_site_option( 'mlp_version', $current_version );
	}

	/**
	 * Moves site relations from deprecated site options to the new custom network table.
	 *
	 * @return void
	 */
	private function import_site_relations() {

		$site_relations = $this->container['multilingualpress.site_relations'];

		// TODO: With WordPress 4.6 + 2, just use `get_sites()`, and remove `$is_pre_4_6`.

		$is_pre_4_6 = version_compare( $GLOBALS['wp_version'], '4.6-RC1', '<' );

		$all_sites = $is_pre_4_6 ? wp_get_sites() : get_sites();
		foreach ( $all_sites as $site ) {
			// TODO: With WordPress 4.6 + 2, just use `$site->id`.
			$site_id = $is_pre_4_6 ? $site['blog_id'] : $site->id;

			$linked = get_blog_option( $site_id, 'inpsyde_multilingual_blog_relationship', [] );
			if ( $linked ) {
				$site_relations->insert_relations( $site_id, $linked );
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

		$table = $this->container['multilingualpress.content_relations_table']->name();

		$this->db->query( "UPDATE $table set ml_type = 'post' WHERE ml_type NOT IN('post','term')" );
	}

	/**
	 * Imports all active languages from the according network option into the languages table.
	 *
	 * @return void
	 */
	private function import_active_languages() {

		$languages = get_network_option( null, 'inpsyde_multilingual', [] );
		if ( ! $languages ) {
			return;
		}

		$table = $this->container['multilingualpress.languages_table']->name();

		$query = "SELECT ID FROM $table WHERE wp_locale = %s OR iso_639_1 = %s";

		array_walk( $languages, function ( array $language ) use ( $table, $query ) {

			$language_id = $this->db->get_var( $this->db->prepare( $query, $language['lang'], $language['lang'] ) );
			if ( $language_id ) {
				$this->db->update(
					$table,
					[ 'priority' => 10 ],
					[ 'ID' => $language_id ]
				);

				return;
			}

			if ( ! isset( $language['lang'] ) ) {
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
