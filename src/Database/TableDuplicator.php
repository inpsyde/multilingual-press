<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

/**
 * Interface for all table duplicator implementations.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
interface TableDuplicator {

	/**
	 * Creates a new table that is an exact duplicate of an existing table.
	 *
	 * @since 3.0.0
	 *
	 * @param string $existing_table Name of the existing table.
	 * @param string $new_table      Name of the new table.
	 *
	 * @return bool Whether or not the table was duplicated successfully.
	 */
	public function duplicate_table( string $existing_table, string $new_table ): bool;
}
