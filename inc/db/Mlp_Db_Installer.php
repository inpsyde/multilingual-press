<?php # -*- coding: utf-8 -*-
/**
 * Class Mlp_Db_Installer
 *
 * Install our tables.
 *
 * @version 2014.07.09
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
	 * @param Mlp_Db_Schema_Interface $db_info
	 */
	public function __construct( Mlp_Db_Schema_Interface $db_info ) {

		$this->db_info = $db_info;
		$this->wpdb    = $GLOBALS['wpdb'];
	}

	/**
	 * Delete table.
	 *
	 * @param Mlp_Db_Schema_Interface $schema
	 * @return  int|false Number of rows affected/selected or false on error
	 */
	public function uninstall( Mlp_Db_Schema_Interface $schema = NULL ) {

		$table = $this->get_schema( $schema )->get_table_name();
		return $this->wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	/**
	 * Create table.
	 *
	 * @param  Mlp_Db_Schema_Interface $schema
	 * @return int Number of table operations run during installation
	 */
	public function install( Mlp_Db_Schema_Interface $schema = NULL ) {

		$db_info         = $this->get_schema( $schema );
		$charset_collate = $this->get_wp_charset_collate();
		$table           = $db_info->get_table_name();
		$columns         = $db_info->get_schema();
		$columns_sql     = $this->array_to_sql_columns( $columns );
		$primary_key     = $db_info->get_primary_key();
		$index_sql       = $db_info->get_index_sql();
		$add             = '';

		if ( ! empty ( $primary_key ) )
			$add .= "PRIMARY KEY  ($primary_key)"; // two spaces!

		if ( ! empty ( $index_sql ) )
			$add .= ", $index_sql";

		// the user could have just deleted the plugin without running the clean up.
		$sql = 'CREATE TABLE ' . $table . ' ( ' . $columns_sql . ' ' . $add . ' ) ' . $charset_collate . ';';

		// make dbDelta() available
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		return (int) $this->insert_default( $db_info, $columns );
	}

	/**
	 * @param Mlp_Db_Schema_Interface $db_info
	 * @param array                   $columns
	 * @return false|int
	 */
	private function insert_default(
		Mlp_Db_Schema_Interface $db_info,
		Array                   $columns
		) {

		$content = $db_info->get_default_content();

		if ( empty ( $content ) )
			return 0;

		$table     = $db_info->get_table_name();
		$to_remove = $db_info->get_autofilled_keys();

		foreach ( $to_remove as $remove_key )
			unset ( $columns[ $remove_key ] );

		$keys     = join( ",", array_keys( $columns ) );
		$sql      = "INSERT INTO $table ( $keys ) VALUES " . $content;

		return $this->wpdb->query( $sql );
	}

	/**
	 * @param  array $array
	 * @return string
	 */
	private function array_to_sql_columns( Array $array ) {

		$out = '';

		foreach ( $array as $key => $properties )
			$out .= "$key $properties,\n";

		return $out;
	}

	/**
	 * Get table charset and collation.
	 *
	 * @return string
	 */
	private function get_wp_charset_collate() {

		// we need multibyte encoding for native names
		if ( ! empty ( $this->wpdb->charset ) && FALSE !== stripos( $this->wpdb->charset, 'utf') )
			$charset_collate = "DEFAULT CHARACTER SET " . $this->wpdb->charset;
		else
			$charset_collate = "DEFAULT CHARACTER SET utf8";

		if ( ! empty ( $this->wpdb->collate ) )
			$charset_collate .= " COLLATE " . $this->wpdb->collate;

		return $charset_collate;
	}

	/**
	 * Helper function for install() and uninstall().
	 *
	 * Basically a check for NULL values.
	 *
	 * @param  Mlp_Db_Schema_Interface $schema
	 * @return Mlp_Db_Schema_Interface
	 */
	private function get_schema( Mlp_Db_Schema_Interface $schema = NULL ) {

		if ( NULL === $schema )
			return $this->db_info;

		return $schema;
	}
}