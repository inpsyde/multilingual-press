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
	 * Returns an array of arrays with all available language data.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed[][] The array of arrays with all available language data.
	 */
	public function get_all_languages();

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
}
