<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Common\Type\Language;

/**
 * Interface for all languages API implementations.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
interface Languages {

	/**
	 * Deletes the language with the given ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $id Language ID.
	 *
	 * @return bool Whether or not the language has been deleted successfully.
	 */
	public function delete_language( int $id ): bool;

	/**
	 * Returns an array with objects of all available languages.
	 *
	 * @since 3.0.0
	 *
	 * @return Language[] The array with objects of all available languages.
	 */
	public function get_all_languages(): array;

	/**
	 * Returns the complete language data of all sites.
	 *
	 * @since 3.0.0
	 *
	 * @return Language[] The array with site IDs as keys and language objects as values.
	 */
	public function get_all_site_languages(): array;

	/**
	 * Returns the language for the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $column Column name to be used for querying.
	 * @param string|int $value  Value to be used for querying.
	 *
	 * @return Language Language object.
	 */
	public function get_language_by( string $column, $value ): Language;

	/**
	 * Returns all languages according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Arguments.
	 *
	 * @return Language[] The array with objects of all languages according to the given arguments.
	 */
	public function get_languages( array $args = [] ): array;

	/**
	 * Imports the given language. An existing language with the same code will be overwritten.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Language data.
	 *
	 * @return bool Whether or not the language has been imported successfully.
	 */
	public function import_language( array $data ): bool;

	/**
	 * Creates a new language entry according to the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Language data.
	 *
	 * @return int Language ID.
	 */
	public function insert_language( array $data ): int;

	/**
	 * Updates the language with the given ID according to the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $id   Language ID.
	 * @param array $data Language data.
	 *
	 * @return bool Whether or not the language has been updated successfully.
	 */
	public function update_language( int $id, array $data ): bool;
}
