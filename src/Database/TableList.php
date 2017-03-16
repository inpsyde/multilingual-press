<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

/**
 * Interface for all table list implementations.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
interface TableList {

	/**
	 * Returns an array with the names of all tables.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The names of all tables.
	 */
	public function all_tables(): array;

	/**
	 * Returns an array with the names of all network tables.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The names of all network tables.
	 */
	public function network_tables(): array;

	/**
	 * Returns an array with the names of all tables for the site with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string[] The names of all tables for the site with the given ID.
	 */
	public function site_tables( int $site_id ): array;
}
