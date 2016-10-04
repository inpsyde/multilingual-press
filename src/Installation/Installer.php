<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Common\Type\VersionNumber;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Performs installation-specific tasks.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
class Installer {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 */
	public function __construct( Container $container ) {

		$this->container = $container;
	}

	/**
	 * Installs the current version of MultilingualPress.
	 *
	 * @since 3.0.0
	 *
	 * @param VersionNumber $version MultilingualPress version number.
	 *
	 * @return bool Whether or not the current version of MultilingualPress was installed successfully.
	 */
	public function install( VersionNumber $version ) {

		$table_installer = $this->container['multilingualpress.table_installer'];

		$table_installer->install( $this->container['multilingualpress.content_relations_table'] );
		$table_installer->install( $this->container['multilingualpress.languages_table'] );
		$table_installer->install( $this->container['multilingualpress.site_relations_table'] );

		// TODO: Don't hardcode the option.
		return update_network_option( null, 'mlp_version', $version );
	}
}
