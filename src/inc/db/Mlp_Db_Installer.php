<?php # -*- coding: utf-8 -*-

/**
 * Class Mlp_Db_Installer
 *
 * Install our tables.
 *
 * @version 2015.06.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Db_Installer implements Mlp_Db_Installer_Interface {

	/**
	 * @var Mlp_Db_Schema_Interface
	 */
	private $db_info;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param Mlp_Db_Schema_Interface $db_info Table information.
	 */
	public function __construct( Mlp_Db_Schema_Interface $db_info ) {

		global $wpdb;

		$this->db_info = $db_info;
		$this->wpdb = $wpdb;
	}

	/**
	 * Delete the table.
	 *
	 * @param Mlp_Db_Schema_Interface $schema Table information.
	 *
	 * @return int|bool Number of rows affected/selected or false on error.
	 */
	public function uninstall( Mlp_Db_Schema_Interface $schema = null ) {

		$schema = $this->get_schema( $schema );

		$table = $schema->get_table_name();

		return $this->wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	/**
	 * Create the table according to the given data.
	 *
	 * @param Mlp_Db_Schema_Interface $schema Table information.
	 *
	 * @return int Number of table operations run during installation.
	 */
	public function install( Mlp_Db_Schema_Interface $schema = null ) {

		// make dbDelta() available
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$db_info = $this->get_schema( $schema );
		$table = $db_info->get_table_name();
		$columns = $db_info->get_schema();
		$columns_sql = $this->array_to_sql_columns( $columns );

		$add = '';

		$primary_key = $db_info->get_primary_key();
		if ( ! empty( $primary_key ) ) {
			// Due to dbDelta: two spaces after PRIMARY KEY!
			$add .= "\tPRIMARY KEY  ($primary_key)";
		}

		$index_sql = $db_info->get_index_sql();
		if ( ! empty( $index_sql ) ) {
			if ( ! empty( $primary_key ) ) {
				$add .= ",\n";
			}
			$add .= "\t$index_sql";
		}

		if ( $add ) {
			$add .= "\n";
		}

		$charset_collate = $this->get_wp_charset_collate();

		// the user could have just deleted the plugin without running the clean up.
		$sql = "CREATE TABLE $table (\n{$columns_sql}{$add})$charset_collate;";
		dbDelta( $sql );

		return (int) $this->insert_default( $db_info, $columns );
	}

	/**
	 * Insert default content into the given table.
	 *
	 * @param Mlp_Db_Schema_Interface $db_info Table information.
	 * @param array                   $columns Table columns.
	 *
	 * @return int|bool
	 */
	private function insert_default( Mlp_Db_Schema_Interface $db_info, array $columns ) {

		$table = $db_info->get_table_name();

		// Bail if the table is not empty
		$temp = $this->wpdb->query( "SELECT 1 FROM $table LIMIT 1" );
		if ( $temp ) {
			return 0;
		}

		$content = $db_info->get_default_content();
		if ( empty( $content ) ) {
			return 0;
		}

		$to_remove = $db_info->get_autofilled_keys();
		foreach ( $to_remove as $remove_key ) {
			unset( $columns[ $remove_key ] );
		}

		$keys = join( ',', array_keys( $columns ) );
		$sql = "INSERT INTO $table ($keys) VALUES $content;";

		return $this->wpdb->query( $sql );
	}

	/**
	 * Return SQL string for given columns.
	 *
	 * @param array $columns Key-properties array of SQL columns.
	 *
	 * @return string
	 */
	private function array_to_sql_columns( array $columns ) {

		$out = '';

		foreach ( $columns as $key => $properties ) {
			$out .= "\t$key $properties,\n";
		}

		return $out;
	}

	/**
	 * Get the table charset and collation.
	 *
	 * @return string
	 */
	private function get_wp_charset_collate() {

		$charset_collate = ' DEFAULT CHARACTER SET ';

		// we need multibyte encoding for native names
		if (
			! empty( $this->wpdb->charset )
			&& false !== stripos( $this->wpdb->charset, 'utf' )
		) {
			$charset_collate .= $this->wpdb->charset;
		} else {
			$charset_collate .= 'utf8';
		}

		if ( ! empty( $this->wpdb->collate ) ) {
			$charset_collate .= ' COLLATE ' . $this->wpdb->collate;
		}

		return $charset_collate;
	}

	/**
	 * Helper function for install() and uninstall().
	 *
	 * Basically a check for null values.
	 *
	 * @param Mlp_Db_Schema_Interface $schema Table information.
	 *
	 * @return Mlp_Db_Schema_Interface
	 */
	private function get_schema( Mlp_Db_Schema_Interface $schema = null ) {

		if ( null === $schema ) {
			return $this->db_info;
		}

		return $schema;
	}

}
