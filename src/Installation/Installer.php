<?php # -*- coding: utf-8 -*-

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
	 * @var Table
	 */
	private $content_relations_table;

	/**
	 * @var Table
	 */
	private $languages_table;

	/**
	 * @var Table
	 */
	private $site_relations_table;

	/**
	 * @var TableInstaller
	 */
	private $table_installer;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TableInstaller $table_installer         Table installer object.
	 * @param Table          $content_relations_table Content relations table object.
	 * @param Table          $languages_table         Languages table object.
	 * @param Table          $site_relations_table    Site relations table object.
	 */
	public function __construct(
		TableInstaller $table_installer,
		Table $content_relations_table,
		Table $languages_table,
		Table $site_relations_table
	) {

		$this->table_installer = $table_installer;

		$this->content_relations_table = $content_relations_table;

		$this->languages_table = $languages_table;

		$this->site_relations_table = $site_relations_table;
	}

	/**
	 * Performs installation-specific tasks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function install() {

		$this->table_installer->install( $this->content_relations_table );
		$this->table_installer->install( $this->languages_table );
		$this->table_installer->install( $this->site_relations_table );
	}
}
