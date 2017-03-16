<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

/**
 * Interface for all table replacer implementations.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
interface TableReplacer {

	/**
	 * Replaces the content of one table with another table's content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $source      Name of the source table.
	 * @param string $destination Name of the destination table.
	 *
	 * @return bool Whether or not the table was replaced successfully.
	 */
	public function replace_table( string $source, string $destination ): bool;
}
