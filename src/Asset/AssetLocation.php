<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Asset location data type.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
class AssetLocation {

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file The relative file name (or path).
	 * @param string $path The local path to the directory containing the file.
	 * @param string $url  The public URL for the directory containing the file.
	 */
	public function __construct( string $file, string $path, string $url ) {

		$this->file = $file;

		$this->path = $path;

		$this->url = $url;
	}

	/**
	 * Returns the relative file name (or path).
	 *
	 * @since 3.0.0
	 *
	 * @return string The relative file name (or path).
	 */
	public function file(): string {

		return $this->file;
	}

	/**
	 * Returns the local path to the directory containing the file.
	 *
	 * @since 3.0.0
	 *
	 * @return string The local path to the directory containing the file.
	 */
	public function path(): string {

		return $this->path;
	}

	/**
	 * Returns the public URL for the directory containing the file.
	 *
	 * @since 3.0.0
	 *
	 * @return string The public URL for the directory containing the file.
	 */
	public function url(): string {

		return $this->url;
	}
}
