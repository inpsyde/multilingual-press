<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Common\PluginProperties;
use Inpsyde\MultilingualPress\Core\Exception\PropertyManipulationNotAllowedException;
use Inpsyde\MultilingualPress\Core\Exception\PropertyNotSetException;

/**
 * Immutable plugin properties implementation.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class ImmutablePluginProperties implements PluginProperties {

	/**
	 * @var array
	 */
	private $properties;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin_file_path Main plugin file path.
	 */
	public function __construct( $plugin_file_path ) {

		if ( ! isset( $this->properties ) ) {
			$file_data = [
				'plugin_base_name' => plugin_basename( $plugin_file_path ),
				'plugin_dir_url'   => plugins_url( '/', $plugin_file_path ),
				'plugin_file_path' => (string) $plugin_file_path,
			];

			$header_data = get_file_data( $plugin_file_path, [
				'plugin_name'      => 'Plugin Name',
				'plugin_website'   => 'Plugin URI',
				'version'          => 'Version',
				'text_domain'      => 'Text Domain',
				'text_domain_path' => 'Domain Path',
			] );

			$this->properties = array_merge( $file_data, $header_data );
		}
	}

	/**
	 * Checks if a property with the given name exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a property.
	 *
	 * @return bool Whether or not a property with the given name exists.
	 */
	public function offsetExists( $name ) {

		return array_key_exists( $name, $this->properties );
	}

	/**
	 * Returns the value of the property with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a property.
	 *
	 * @return mixed The value of the property with the given name.
	 *
	 * @throws PropertyNotSetException if there is no property with the given name.
	 */
	public function offsetGet( $name ) {

		if ( ! $this->offsetExists( $name ) ) {
			throw PropertyNotSetException::for_name( $name, 'read' );
		}

		return $this->properties[ $name ];
	}

	/**
	 * Stores the given value with the given name.
	 *
	 * Setting properties is not allowed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name  The name of a property.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @throws PropertyManipulationNotAllowedException
	 */
	public function offsetSet( $name, $value ) {

		throw PropertyManipulationNotAllowedException::for_name( $name, 'set' );
	}

	/**
	 * Removes the property with the given name.
	 *
	 * Removing properties is not allowed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a property.
	 *
	 * @return void
	 *
	 * @throws PropertyManipulationNotAllowedException
	 */
	public function offsetUnset( $name ) {

		throw PropertyManipulationNotAllowedException::for_name( $name, 'unset' );
	}

	/**
	 * Returns the base name of the plugin.
	 *
	 * @since 3.0.0
	 *
	 * @return string The base name of the plugin.
	 */
	public function plugin_base_name() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the URL of the plugin root folder.
	 *
	 * @since 3.0.0
	 *
	 * @return string The URL of the plugin root folder.
	 */
	public function plugin_dir_url() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the absolute path of main plugin file.
	 *
	 * @since 3.0.0
	 *
	 * @return string The absolute path of main plugin file.
	 */
	public function plugin_file_path() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the plugin name as given in the plugin headers.
	 *
	 * @since 3.0.0
	 *
	 * @return string The plugin name.
	 */
	public function plugin_name() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the URL of the plugin website.
	 *
	 * @since 3.0.0
	 *
	 * @return string The URL of the plugin website
	 */
	public function plugin_website() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the plugin version.
	 *
	 * @since 3.0.0
	 *
	 * @return string The plugin version.
	 */
	public function version() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the plugin text domain.
	 *
	 * @since 3.0.0
	 *
	 * @return string The plugin text domain.
	 */
	public function text_domain() {

		return $this->offsetGet( __FUNCTION__ );
	}

	/**
	 * Returns the absolute path of the folder with the plugin translation files.
	 *
	 * @since 3.0.0
	 *
	 * @return string The absolute path of the folder with the plugin translation files.
	 */
	public function text_domain_path() {

		return $this->offsetGet( __FUNCTION__ );
	}
}
