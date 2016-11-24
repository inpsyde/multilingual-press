<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Database\Table;
use wpdb;

/**
 * Languages API implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class WPDBLanguages implements Languages {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $table;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Table $table Site relations table object.
	 */
	public function __construct( Table $table ) {

		$this->table = $table->name();

		$this->db = $GLOBALS['wpdb'];
	}

	/**
	 * Returns an array of arrays with all available language data.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed[][] The array of arrays with all available language data.
	 */
	public function get_all_languages() {

		$query = "SELECT * FROM {$this->table} ORDER BY priority DESC, english_name ASC";

		$result = $this->db->get_results( $query, ARRAY_A );

		return is_array( $result ) ? $result : [];
	}

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
	) {

		$query = $this->db->prepare( "SELECT * FROM {$this->table} WHERE http_name = %s LIMIT 1", $http_code );

		$results = $this->db->get_row( $query, ARRAY_A );

		if ( 'all' === $field ) {
			return is_array( $results ) ? $results : [];
		}

		foreach ( array_unique( array_merge( (array) $field, (array) $fallbacks ) ) as $key ) {
			if ( ! empty( $results[ $key ] ) ) {
				return (string) $results[ $key ];
			}
		}

		return '';
	}
}
