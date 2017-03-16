<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Interface for all locations implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface Locations {

	/**
	 * Location type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_PATH = 'path';

	/**
	 * Location type.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_URL = 'url';

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
	public function add( string $name, string $path, string $url ): Locations;

	/**
	 * Returns the location data according to the given arguments.
	 *
	 * @param string $name Location name.
	 * @param string $type Location type.
	 *
	 * @return string Location data.
	 */
	public function get( string $name, string $type ): string;

	/**
	 * Checks if a location with the given name exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name Location name.
	 *
	 * @return bool Whether or not a location with the given name exists.
	 */
	public function has( string $name ): bool;
}
