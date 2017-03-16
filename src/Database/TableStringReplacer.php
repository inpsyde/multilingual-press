<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

/**
 * Interface for all table string replacer implementations.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
interface TableStringReplacer {

	/**
	 * Replaces one string with another all given columns of the given table at once.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $table       The name of the table to replace the string in.
	 * @param string[] $columns     The names of all columns to replace the string in.
	 * @param string   $search      The string to replace.
	 * @param string   $replacement The replacement.
	 *
	 * @return int The number of affected rows.
	 */
	public function replace_string(
		string $table,
		array $columns,
		string $search,
		string $replacement
	) : int;
}
