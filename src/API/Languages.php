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
	 * @param array $language Language data.
	 *
	 * @return bool Whether or not the language has been imported successfully.
	 */
	public function import_language( array $language ): bool;

	/**
	 * Updates the given languages.
	 *
	 * @since 3.0.0
	 *
	 * @param array $languages An array with language IDs as keys and one or more fields as values.
	 *
	 * @return int The number of updated languages.
	 */
	public function update_languages_by_id( array $languages ): int;
}
