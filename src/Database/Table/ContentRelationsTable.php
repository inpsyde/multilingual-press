<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database\Table;

use Inpsyde\MultilingualPress\Database\Table;

// TODO: This (as well as the Content Relations API) will be (functionally) refactored after the structural one.

/**
 * Content relations table.
 *
 * @package Inpsyde\MultilingualPress\Database\Table
 * @since   3.0.0
 */
final class ContentRelationsTable implements Table {

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
			'ml_id',
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
		return "KEY (ml_blogid,ml_elementid)";
	}

	/**
	 * Returns the table name.
	 *
	 * @since 3.0.0
	 *
	 * @return string The table name.
	 */
	public function name() {

		return "{$this->prefix}multilingual_linked";
	}

	/**
	 * Returns the primary key.
	 *
	 * @since 3.0.0
	 *
	 * @return string The primary key.
	 */
	public function primary_key() {

		return 'ml_id';
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
			'ml_id'               => 'int unsigned NOT NULL AUTO_INCREMENT',
			'ml_source_blogid'    => 'bigint(20) NOT NULL',
			'ml_source_elementid' => 'bigint(20) NOT NULL',
			'ml_blogid'           => 'bigint(20) NOT NULL',
			'ml_elementid'        => 'bigint(20) NOT NULL',
			'ml_type'             => 'varchar(20) NOT NULL',
		];
	}
}
