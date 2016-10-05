<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database\Table;

use Inpsyde\MultilingualPress\Database\Table;

/**
 * Site relations table.
 *
 * @package Inpsyde\MultilingualPress\Database\Table
 * @since   3.0.0
 */
final class SiteRelationsTable implements Table {

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $prefix Optional Table name prefix. Defaults to empty string.
	 */
	public function __construct( $prefix = '' ) {

		$this->prefix = (string) $prefix;
	}

	/**
	 * Returns an array with all columns that do not have any default content.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] All columns that do not have any default content.
	 */
	public function columns_without_default_content() {

		return [
			'ID',
		];
	}

	/**
	 * Returns the SQL string for the default content.
	 *
	 * @since 3.0.0
	 *
	 * @return string The SQL string for the default content.
	 */
	public function default_content_sql() {

		return '';
	}

	/**
	 * Returns the SQL string for all (unique) keys.
	 *
	 * @since 3.0.0
	 *
	 * @return string The SQL string for all (unique) keys.
	 */
	public function keys_sql() {

		// Due to dbDelta: KEY (not INDEX), and no spaces inside brackets!
		return "UNIQUE KEY site_combinations (site_1,site_2)";
	}

	/**
	 * Returns the table name.
	 *
	 * @since 3.0.0
	 *
	 * @return string The table name.
	 */
	public function name() {

		return "{$this->prefix}mlp_site_relations";
	}

	/**
	 * Returns the primary key.
	 *
	 * @since 3.0.0
	 *
	 * @return string The primary key.
	 */
	public function primary_key() {

		return 'ID';
	}

	/**
	 * Returns the table schema.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with fields as keys and the according SQL definitions as values.
	 */
	public function schema() {

		return [
			'ID'     => 'int unsigned NOT NULL AUTO_INCREMENT',
			'site_1' => 'bigint(20) NOT NULL',
			'site_2' => 'bigint(20) NOT NULL',
		];
	}
}
