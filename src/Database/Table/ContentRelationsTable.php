<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Database\Table;

use Inpsyde\MultilingualPress\Database\Table;

/**
 * Content relations table.
 *
 * @package Inpsyde\MultilingualPress\Database\Table
 * @since   3.0.0
 */
final class ContentRelationsTable implements Table {

	/**
	 * Column name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const COLUMN_CONTENT_ID = 'content_id';

	/**
	 * Column name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const COLUMN_RELATIONSHIP_ID = 'relationship_id';

	/**
	 * Column name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const COLUMN_SITE_ID = 'site_id';

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $prefix Optional Table name prefix. Defaults to empty string.
	 */
	public function __construct( string $prefix = '' ) {

		$this->prefix = $prefix;
	}

	/**
	 * Returns an array with all columns that do not have any default content.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] All columns that do not have any default content.
	 */
	public function columns_without_default_content(): array {

		return [];
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
			'KEY site_content (%1$s,%2$s)',
			self::COLUMN_SITE_ID,
			self::COLUMN_CONTENT_ID
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

		return "{$this->prefix}mlp_content_relations";
	}

	/**
	 * Returns the primary key.
	 *
	 * @since 3.0.0
	 *
	 * @return string The primary key.
	 */
	public function primary_key(): string {

		return sprintf(
			'%1$s,%2$s,%3$s',
			self::COLUMN_RELATIONSHIP_ID,
			self::COLUMN_SITE_ID,
			self::COLUMN_CONTENT_ID
		);
	}

	/**
	 * Returns the table schema.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] An array with fields as keys and the according SQL definitions as values.
	 */
	public function schema(): array {

		return [
			self::COLUMN_RELATIONSHIP_ID => 'bigint(20) unsigned NOT NULL auto_increment',
			self::COLUMN_SITE_ID         => 'bigint(20) NOT NULL',
			self::COLUMN_CONTENT_ID      => 'bigint(20) NOT NULL',
		];
	}
}
