<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

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
	 * @return Locations Locations instance.
	 */
	public function add( string $name, string $path, string $url ): Locations {

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
	public function get( string $name, string $type ): string {

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
	public function has( string $name ): bool {

		return array_key_exists( $name, $this->locations );
	}
}
