<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Database;

/**
 * Interface for all table installer implementations.
 *
 * @package Inpsyde\MultilingualPress\Database
 * @since   3.0.0
 */
interface TableInstaller {

	/**
	 * Installs the given table.
	 *
	 * @since 3.0.0
	 *
	 * @param Table $table Optional. Table object. Defaults to null.
	 *
	 * @return bool Whether or not the table was installed successfully.
	 */
	public function install( Table $table = null ): bool;

	/**
	 * Uninstalls the given table.
	 *
	 * @since 3.0.0
	 *
	 * @param Table $table Optional. Table object. Defaults to null.
	 *
	 * @return bool Whether or not the table was uninstalled successfully.
	 */
	public function uninstall( Table $table = null ): bool;
}
