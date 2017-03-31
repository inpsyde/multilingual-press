<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

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
	 * Column name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const COLUMN_ID = 'ID';

	/**
	 * Column name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const COLUMN_SITE_1 = 'site_1';

	/**
	 * Column name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const COLUMN_SITE_2 = 'site_2';

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
	public function __construct( string $prefix = '' ) {

		$this->prefix = (string) $prefix;
	}

	/**
	 * Returns an array with all columns that do not have any default content.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] All columns that do not have any default content.
	 */
	public function columns_without_default_content(): array {

		return [
			self::COLUMN_ID,
		];
	}

	/**
	 * Returns the SQL string for the default content.
	 *
	 * @since 3.0.0
	 *
	 * @return string The SQL string for the default content.
	 */
	public function default_content_sql(): string {

		return '';
	}

	/**
	 * Returns the SQL string for all (unique) keys.
	 *
	 * @since 3.0.0
	 *
	 * @return string The SQL string for all (unique) keys.
	 */
	public function keys_sql(): string {

		// Due to dbDelta: KEY (not INDEX), and no spaces inside brackets!
		return sprintf(
			'UNIQUE KEY site_combinations (%1$s,%2$s)',
			self::COLUMN_SITE_1,
			self::COLUMN_SITE_2
		);
	}

	/**
	 * Returns the table name.
	 *
	 * @since 3.0.0
	 *
	 * @return string The table name.
	 */
	public function name(): string {

		return "{$this->prefix}mlp_site_relations";
	}

	/**
	 * Returns the primary key.
	 *
	 * @since 3.0.0
	 *
	 * @return string The primary key.
	 */
	public function primary_key(): string {

		return self::COLUMN_ID;
	}

	/**
	 * Returns the table schema.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with column names as keys and the according SQL definitions as values.
	 */
	public function schema(): array {

		return [
			self::COLUMN_ID     => 'int unsigned NOT NULL AUTO_INCREMENT',
			self::COLUMN_SITE_1 => 'bigint(20) NOT NULL',
			self::COLUMN_SITE_2 => 'bigint(20) NOT NULL',
		];
	}
}
