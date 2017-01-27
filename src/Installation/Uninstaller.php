<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\TableInstaller;

/**
 * MultilingualPress uninstaller.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class Uninstaller {

	/**
	 * @var int[]
	 */
	private $site_ids;

	/**
	 * @var TableInstaller
	 */
	private $table_installer;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TableInstaller $table_installer Table installer object.
	 */
	public function __construct( TableInstaller $table_installer ) {

		$this->table_installer = $table_installer;
	}

	/**
	 * Checks if the uninstall request is valid.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the uninstall request is valid.
	 */
	public function is_request_valid() {

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		if ( ! is_multisite() ) {
			return false;
		}

		return true;
	}

	/**
	 * Uninstalls the given tables.
	 *
	 * @since 3.0.0
	 *
	 * @param Table[] $tables Table objects.
	 *
	 * @return int The number of uninstalled tables.
	 */
	public function uninstall_tables( array $tables ) {

		return array_reduce( $tables, function ( $uninstalled, Table $table ) {

			return $uninstalled + (int) $this->table_installer->uninstall( $table->name() );
		}, 0 );
	}

	/**
	 * Deletes all MultilingualPress network options.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $options Option names.
	 *
	 * @return int The number of deleted options.
	 */
	public function delete_network_options( array $options ) {

		return array_reduce( $options, function ( $deleted, $option ) {

			return $deleted + (int) delete_network_option( null, $option );
		}, 0 );
	}

	/**
	 * Deletes all MultilingualPress options for the given (or all) sites.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $options  Option names.
	 * @param int[]    $site_ids Optional. Site IDs. Defaults to empty string.
	 *
	 * @return int The number of deleted options.
	 */
	public function delete_site_options( array $options, array $site_ids = [] ) {

		$site_ids = $site_ids ?: $this->site_ids();

		return array_reduce( $site_ids, function ( $deleted, $site_id ) use ( $options ) {

			switch_to_blog( $site_id );

			$deleted += array_reduce( $options, function ( $deleted, $option ) {

				return $deleted + (int) delete_option( $option );
			}, $deleted );

			restore_current_blog();

			return $deleted;
		}, 0 );
	}

	/**
	 * Returns an array with all site IDs.
	 *
	 * @return int[] Site IDs.
	 */
	private function site_ids() {

		if ( ! isset( $this->site_ids ) ) {
			$this->site_ids = wp_list_pluck( get_sites(), 'id' );
		}

		return $this->site_ids;
	}
}
