<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\API;

use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\NullLanguage;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;
use Inpsyde\MultilingualPress\Factory\TypeFactory;

/**
 * Languages API implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\API
 * @since   3.0.0
 */
final class WPDBLanguages implements Languages {

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var string[]
	 */
	private $fields;

	/**
	 * @var SiteSettingsRepository
	 */
	private $site_settings_repository;

	/**
	 * @var string
	 */
	private $table;

	/**
	 * @var TypeFactory
	 */
	private $type_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param \wpdb                  $db                       WordPress database object.
	 * @param Table                  $table                    Site relations table object.
	 * @param SiteSettingsRepository $site_settings_repository Site settings repository object.
	 * @param TypeFactory            $type_factory             Type factory object.
	 */
	public function __construct(
		\wpdb $db,
		Table $table,
		SiteSettingsRepository $site_settings_repository,
		TypeFactory $type_factory
	) {

		$this->db = $db;

		$this->table = $table->name();

		$this->site_settings_repository = $site_settings_repository;

		$this->type_factory = $type_factory;

		$this->fields = $this->extract_field_specifications_from_table( $table );
	}

	/**
	 * Returns an array with objects of all available languages.
	 *
	 * @since 3.0.0
	 *
	 * @return Language[] The array with objects of all available languages.
	 */
	public function get_all_languages(): array {

		$query = sprintf(
			'SELECT * FROM %1$s ORDER BY %2$s DESC, %3$s ASC',
			$this->table,
			LanguagesTable::COLUMN_PRIORITY,
			LanguagesTable::COLUMN_ENGLISH_NAME
		);

		$results = $this->db->get_results( $query, ARRAY_A );
		if ( ! $results || ! is_array( $results ) ) {
			return [];
		}

		return array_map( [ $this, 'create_language_for_data' ], $results );
	}

	/**
	 * Returns the complete language data of all sites.
	 *
	 * @since 3.0.0
	 *
	 * @return Language[] The array with site IDs as keys and language objects as values.
	 */
	public function get_all_site_languages(): array {

		$languages = $this->site_settings_repository->get_settings();
		if ( ! $languages ) {
			return [];
		}

		$names = [];

		$iso_codes = [];

		foreach ( $languages as $site_id => $language ) {
			if ( ! empty( $language['lang'] ) ) {
				$names[ $site_id ] = str_replace( '_', '-', $language['lang'] );
			} elseif ( ! empty( $language['text'] ) && preg_match( '~[a-zA-Z-]+~', $language['text'] ) ) {
				$names[ $site_id ] = str_replace( '_', '-', $language['text'] );
			}

			if ( isset( $names[ $site_id ] ) && false === strpos( $names[ $site_id ], '-' ) ) {
				$names[ $site_id ] = strtolower( $names[ $site_id ] );

				$iso_codes[ $site_id ] = $names[ $site_id ];
			}

			unset( $languages[ $site_id ]['lang'] );
		}

		$names_string = "'" . implode( "','", array_map( 'esc_sql', $names ) ) . "'";

		$iso_codes_string = $iso_codes
			? "'" . implode( "','", array_map( 'esc_sql', $iso_codes ) ) . "'"
			: '';

		$query = sprintf(
			'SELECT * FROM %1$s WHERE %2$s IN (%3$s)',
			$this->table,
			LanguagesTable::COLUMN_HTTP_CODE,
			$names_string
		);

		if ( $iso_codes ) {
			$query .= sprintf(
				'OR %1$s IN (%2$s)',
				LanguagesTable::COLUMN_ISO_639_1_CODE,
				$iso_codes_string
			);
		}

		foreach ( (array) $this->db->get_results( $query, ARRAY_A ) as $language ) {
			foreach ( $names as $site_id => $name ) {
				if (
					in_array( $name, $language, true )
					|| (
						isset( $iso_codes[ $site_id ] )
						&& $language[ LanguagesTable::COLUMN_ISO_639_1_CODE ] === $iso_codes[ $site_id ]
					)
				) {
					$languages[ $site_id ] += $language;
				}
			}
		}

		return array_map( [ $this, 'create_language_for_data' ], $languages );
	}

	/**
	 * Returns language with the given HTTP code.
	 *
	 * @since 3.0.0
	 *
	 * @param string $http_code Language HTTP code.
	 *
	 * @return Language Language object.
	 */
	public function get_language_by_http_code( string $http_code ): Language {

		// Note: Placeholders intended for \wpdb::prepare() have to be double-encoded for sprintf().
		$query = sprintf(
			'SELECT * FROM %1$s WHERE %2$s = %%s LIMIT 1',
			$this->table,
			LanguagesTable::COLUMN_HTTP_CODE
		);
		$query = $this->db->prepare( $query, $http_code );

		$language = $this->db->get_row( $query, ARRAY_A );
		if ( ! $language || ! is_array( $language ) ) {
			return new NullLanguage();
		}

		return $this->type_factory->create_language( [ $language ] );
	}

	/**
	 * Returns all languages according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Arguments.
	 *
	 * @return Language[] The array with objects of all languages according to the given arguments.
	 */
	public function get_languages( array $args = [] ): array {

		// TODO: Think about what to do with this. Pass Language constants, or LanguagesTable constants, or allow both?
		$args = array_merge( [
			'conditions' => [],
			'fields'     => [],
			'number'     => 0,
			'order_by'   => [
				[
					'field' => LanguagesTable::COLUMN_PRIORITY,
					'order' => 'DESC',
				],
				[
					'field' => LanguagesTable::COLUMN_ENGLISH_NAME,
					'order' => 'ASC',
				],
			],
			'page'       => 1,
		], $args );

		$fields = $this->get_fields( $args );

		$where = $this->get_where( $args );

		$order_by = $this->get_order_by( $args );

		$limit = $this->get_limit( $args );

		$query = "SELECT $fields FROM {$this->table} {$where} {$order_by} {$limit}";

		$results = $this->db->get_results( $query, ARRAY_A );
		if ( ! $results || ! is_array( $results ) ) {
			return [];
		}

		return array_map( [ $this, 'create_language_for_data' ], $results );
	}

	/**
	 * Updates the given languages.
	 *
	 * @since 3.0.0
	 *
	 * @param array $languages An array with language IDs as keys and one or more fields as values.
	 *
	 * @return int The number of updated languages.
	 */
	public function update_languages_by_id( array $languages ): int {

		$updated = 0;

		// TODO: Think about what to do with this. Allow Language constants, or LanguagesTable constants, or both?
		foreach ( $languages as $id => $language ) {
			$updated += (int) $this->db->update(
				$this->table,
				(array) $language,
				[ LanguagesTable::COLUMN_ID => $id ],
				$this->get_field_specifications( $language ),
				'%d'
			);
		}

		return $updated;
	}

	/**
	 * Returns a new language object, instantiated with the given data.
	 *
	 * @param array $data Language data.
	 *
	 * @return Language Language object.
	 */
	private function create_language_for_data( array $data ) {

		return $this->type_factory->create_language( [ $data ] );
	}

	/**
	 * Returns an array with column names as keys and the individual printf conversion specification as value.
	 *
	 * There are a lot more conversion specifications, but we don't need more than telling a string from an int.
	 *
	 * @param Table $table Table object.
	 *
	 * @return string[] The array with column names as keys and the individual printf conversion specification as value.
	 */
	private function extract_field_specifications_from_table( Table $table ): array {

		$numeric_types = implode( '|', [
			'BIT',
			'DECIMAL',
			'DOUBLE',
			'FLOAT',
			'INT',
			'NUMERIC',
			'REAL',
		] );

		$schema = $table->schema();

		return array_combine( array_keys( $schema ), array_map( function ( $definition ) use ( $numeric_types ) {

			return preg_match( '/^\s*[A-Z]*(' . $numeric_types . ')/', $definition ) ? '%d' : '%s';
		}, $schema ) );
	}

	/**
	 * Returns an array with the according specifications for all fields included in the given language.
	 *
	 * @param array $language Language data.
	 *
	 * @return array The array with the according specifications for all fields included in the given language.
	 */
	private function get_field_specifications( array $language ): array {

		return array_map( function ( $field ) {

			return $this->fields[ $field ] ?? '%s';
		}, array_keys( $language ) );
	}

	/**
	 * Returns the according string with all valid fields included in the given arguments, or '*' if none.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string The according string with all valid fields included in the given arguments, or '*' if none.
	 */
	private function get_fields( array $args ): string {

		if ( ! empty( $args['fields'] ) ) {
			$allowed_fields = array_intersect( (array) $args['fields'], array_keys( $this->fields ) );
			if ( $allowed_fields ) {
				return implode( ', ', esc_sql( $allowed_fields ) );
			}
		}

		return '*';
	}

	/**
	 * Returns the according LIMIT string for the number and page values included in the given arguments.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string The according LIMIT string for the number and page values included in the given arguments.
	 */
	private function get_limit( array $args ): string {

		if ( ! empty( $args['number'] ) && 0 < $args['number'] ) {
			$number = (int) $args['number'];

			$start = ( empty( $args['page'] ) && 2 > $args['page'] )
				? 0
				: ( $args['page'] - 1 ) * $number;

			$end = $start + $number;

			return "LIMIT $start, $end";
		}

		return '';
	}

	/**
	 * Returns the according ORDER BY string for all valid fields included in the given arguments.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string The according ORDER BY string for all valid fields included in the given arguments.
	 */
	private function get_order_by( array $args ): string {

		if ( ! empty( $args['order_by'] ) ) {
			$order_by = array_filter( (array) $args['order_by'], [ $this, 'is_array_with_valid_field' ] );
			if ( $order_by ) {
				$order_by = array_map( function ( array $order_by ) {

					$order = empty( $order_by['order'] ) || 'DESC' !== strtoupper( $order_by['order'] )
						? 'ASC'
						: 'DESC';

					return "{$order_by['field']} {$order}";
				}, $order_by );

				return 'ORDER BY ' . implode( ', ', $order_by );
			}
		}

		return '';
	}

	/**
	 * Returns the according WHERE string for all valid conditions included in the given arguments.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string The according WHERE string for all valid conditions included in the given arguments.
	 */
	private function get_where( array $args ): string {

		if ( empty( $args['conditions'] ) ) {
			return '';
		}

		$conditions = array_filter( (array) $args['conditions'], [ $this, 'is_condition_valid' ] );
		if ( ! $conditions ) {
			return '';
		}

		$conditions = array_map( function ( array $condition ) {

			return $this->db->prepare(
				"{$condition['field']} {$condition['compare']} {$this->fields[ $condition['field'] ]}",
				$condition['value']
			);
		}, $conditions );

		return 'WHERE ' . implode( ' AND ', $conditions );
	}

	/**
	 * Checks if the given condition is valid with respect to the defined fields and comparison operators.
	 *
	 * @param mixed $condition Condition.
	 *
	 * @return bool Whether or not the condition is valid with respect to the defined fields and comparison operators.
	 */
	private function is_condition_valid( $condition ): bool {

		if ( ! $this->is_array_with_valid_field( $condition ) ) {
			return false;
		}

		if ( empty( $condition['value'] ) ) {
			return false;
		}

		if ( empty( $condition['compare'] ) ) {
			return true;
		}

		static $comparison_operators;
		if ( ! $comparison_operators ) {
			$comparison_operators = [
				'=',
				'<=>',
				'>',
				'>=',
				'<',
				'<=',
				'LIKE',
				'!=',
				'<>',
				'NOT LIKE',
			];
		}

		return in_array( $condition['compare'], $comparison_operators, true );
	}

	/**
	 * Checks if the given element is an array that has a valid field element.
	 *
	 * @param mixed $maybe_array Maybe an array
	 *
	 * @return bool Whether or not the given element is an array that has a valid field element.
	 */
	private function is_array_with_valid_field( $maybe_array ): bool {

		return
			is_array( $maybe_array )
			&& ! empty( $maybe_array['field'] )
			&& is_scalar( $maybe_array['field'] )
			&& array_key_exists( $maybe_array['field'], $this->fields );
	}
}
