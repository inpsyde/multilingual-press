<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Common\Locations;

/**
 * MultilingualPress-specific locations implementation.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class InternalLocations implements Locations {

	/**
	 * @var string[][]
	 */
	private $locations = [];

	/**
	 * Adds a new location according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name Location name.
	 * @param string $path Path data.
	 * @param string $url  URL data.
	 *
	 * @return static Locations instance.
	 */
	public function add( $name, $path, $url ) {

		$this->locations[ $name ] = [
			Locations::TYPE_PATH => rtrim( $path, '/' ),
			Locations::TYPE_URL  => rtrim( $url, '/' ) . '/',
		];

		return $this;
	}

	/**
	 * Returns the location data according to the given arguments.
	 *
	 * @param string $name Location name.
	 * @param string $type Location type.
	 *
	 * @return string Location data.
	 */
	public function get( $name, $type ) {

		return empty( $this->locations[ $name ][ $type ] )
			? ''
			: $this->locations[ $name ][ $type ];
	}

	/**
	 * Checks if a location with the given name exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name Location name.
	 *
	 * @return bool Whether or not a location with the given name exists.
	 */
	public function has( $name ) {

		return array_key_exists( $name, $this->locations );
	}
}
