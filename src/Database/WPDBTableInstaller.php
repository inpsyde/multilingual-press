<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

use Inpsyde\MultilingualPress\Database\Exception\InvalidTableException;
use wpdb;

/**
 * Table installer implementation using the WordPress database object.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
class WPDBTableInstaller implements TableInstaller {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var string
	 */
	private $options;

	/**
	 * @var Table
	 */
	private $table;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Table $table Optional. Table object. Defaults to null.
	 */
	public function __construct( Table $table = null ) {

		$this->table = $table;

		$this->db = $GLOBALS['wpdb'];
	}

	/**
	 * Installs the given table.
	 *
	 * @since 3.0.0
	 *
	 * @param Table $table Optional. Table object. Defaults to null.
	 *
	 * @return bool Whether or not the table was installed successfully.
	 *
	 * @throws InvalidTableException if a table was neither passed, nor injected via the constructor.
	 */
	public function install( Table $table = null ) {

		$table = $table ?: $this->table;
		if ( ! $table ) {
			throw InvalidTableException::for_action( 'install' );
		}

		$table_name = $table->name();

		$schema = $table->schema();

		$columns = $this->get_columns( $schema );

		$keys = $this->get_keys( $table );

		$options = $this->get_options();

		/**
		 * WordPress file with the dbDelta() function.
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( "CREATE TABLE $table_name ({$columns}{$keys}) $options;" );

		if ( ! $this->db->query( $this->db->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) {
			return false;
		}

		$this->insert_default_content( $table );

		return true;
	}

	/**
	 * Uninstalls the given table.
	 *
	 * @since 3.0.0
	 *
	 * @param Table $table Optional. Table object. Defaults to null.
	 *
	 * @return bool Whether or not the table was uninstalled successfully.
	 *
	 * @throws InvalidTableException if a table was neither passed, nor injected via the constructor.
	 */
	public function uninstall( Table $table = null ) {

		$table = $table ?: $this->table;
		if ( ! $table ) {
			throw InvalidTableException::for_action( 'uninstall' );
		}

		return false !== $this->db->query( 'DROP TABLE IF EXISTS ' . $table->name() );
	}

	/**
	 * Returns the according SQL string for the columns in the given table schema.
	 *
	 * @param array $schema Table schema.
	 *
	 * @return string The SQL string for the columns in the given table schema.
	 */
	private function get_columns( array $schema ) {

		$sql = '';

		array_walk( $schema, function ( $definition, $name ) use ( &$sql ) {

			$sql .= "\n\t$name $definition,";
		} );

		// Remove trailing comma.
		return substr( $sql, 0, -1 );
	}

	/**
	 * Returns the according SQL string for the keys of the given table.
	 *
	 * @param Table $table Table object.
	 *
	 * @return string The SQL string for the keys of the given table.
	 */
	private function get_keys( Table $table ) {

		$keys = '';

		$primary_key = $table->primary_key();
		if ( $primary_key ) {
			// Due to dbDelta: two spaces after PRIMARY KEY!
			$keys .= ",\n\tPRIMARY KEY  ($primary_key)";
		}

		$keys_sql = $table->keys_sql();
		if ( $keys_sql ) {
			$keys .= ",\n\t$keys_sql";
		}

		return "$keys\n";
	}

	/**
	 * Returns the SQL string for the table options.
	 *
	 * @return string
	 */
	private function get_options() {

		if ( isset( $this->options ) ) {
			return $this->options;
		}

		$options = 'DEFAULT CHARACTER SET ';

		// MultilingualPress requires multibyte encoding for native names of languages.
		$options .= ( empty( $this->db->charset ) || false === stripos( $this->db->charset, 'utf' ) )
			? 'utf8'
			: $this->db->charset;

		if ( ! empty( $this->db->collate ) ) {
			$options .= ' COLLATE ' . $this->db->collate;
		}

		$this->options = $options;

		return $this->options;
	}

	/**
	 * Inserts the according default content into the given table.
	 *
	 * @param Table $table Table object.
	 *
	 * @return void
	 */
	private function insert_default_content( Table $table ) {

		$table_name = $table->name();

		// Bail if the table is not empty.
		if ( $this->db->query( "SELECT 1 FROM $table_name LIMIT 1" ) ) {
			return;
		}

		$default_content = $table->default_content_sql();

		if ( empty( $default_content ) ) {
			return;
		}

		$columns = array_keys( $table->schema() );
		$columns = array_diff( $columns, $table->columns_without_default_content() );
		$columns = implode( ',', $columns );

		$this->db->query( "INSERT INTO $table_name ($columns) VALUES $default_content;" );
	}
}
