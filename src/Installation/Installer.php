<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Database\TableInstaller;

/**
 * MultilingualPress installer.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class Installer {

	/**
	 * @var TableInstaller
	 */
	private $table_installer;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TableInstaller $table_installer Table installer object.
	 */
	public function __construct( TableInstaller $table_installer ) {

		$this->table_installer = $table_installer;
	}

	/**
	 * Installs the given tables.
	 *
	 * @since 3.0.0
	 *
	 * @param Table[] ...$tables Table objects.
	 *
	 * @return void
	 */
	public function install_tables( Table ...$tables ) {

		foreach ( $tables as $table ) {
			$this->table_installer->install( $table );
		}
	}
}
