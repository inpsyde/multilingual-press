<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

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
	 * @return object[] The array with objects of all available languages.
	 */
	public function get_all_languages();

	/**
	 * Returns the complete language data of all sites.
	 *
	 * @since 3.0.0
	 *
	 * @return array[] The array with site IDs as keys and arrays with all language data as values.
	 */
	public function get_all_site_languages();

	/**
	 * Returns the desired field value of the language with the given HTTP code.
	 *
	 * @since 3.0.0
	 *
	 * @param string          $http_code Language HTTP code.
	 * @param string          $field     Optional. The field which should be queried. Defaults to 'native_name'.
	 * @param string|string[] $fallbacks Optional. Falback language fields. Defaults to native and English name.
	 *
	 * @return string|string[] The desired field value, an empty string on failure, or an array for field 'all'.
	 */
	public function get_language_by_http_code(
		$http_code,
		$field = 'native_name',
		$fallbacks = [
			'native_name',
			'english_name',
		]
	);

	/**
	 * Returns all languages according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Arguments.
	 *
	 * @return object[] The array with objects of all languages according to the given arguments.
	 */
	public function get_languages( array $args = [] );

	/**
	 * Updates the given languages.
	 *
	 * @since 3.0.0
	 *
	 * @param array $languages An array with language IDs as keys and one or more fields as values.
	 *
	 * @return int The number of updated languages.
	 */
	public function update_languages_by_id( array $languages );
}
