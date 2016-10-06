<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Debug-aware style data type implementation.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
final class DebugAwareStyle implements Style {

	/**
	 * @var string[]
	 */
	private $dependencies;

	/**
	 * @var string
	 */
	private $handle;

	/**
	 * @var string
	 */
	private $media;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string|null
	 */
	private $version = null;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string      $handle       The handle.
	 * @param string      $url          The public URL for the directory containing the file.
	 * @param string[]    $dependencies Optional. The dependencies. Defaults to empty array.
	 * @param string|null $version      Optional. Version of the file. Defaults to empty string.
	 * @param string      $media        Optional. Style media data. Defaults to 'all'.
	 */
	public function __construct(
		$handle,
		$url,
		array $dependencies = [],
		$version = '',
		$media = 'all'
	) {

		$this->handle = (string) $handle;

		$this->url = (string) $url;

		$this->dependencies = array_map( 'strval', $dependencies );

		if ( null !== $version ) {
			$this->version = (string) $version;
		}

		$this->media = (string) $media;
	}

	/**
	 * Returns a new style object, instantiated according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string        $handle       The handle.
	 * @param AssetLocation $location     Location object.
	 * @param string[]      $dependencies Optional. The dependencies. Defaults to empty array.
	 * @param string|null   $version      Optional. Version of the file. Defaults to empty string.
	 * @param string        $media        Optional. Style media data. Defaults to 'all'.
	 *
	 * @return static Style object.
	 */
	public static function from_location(
		$handle,
		AssetLocation $location,
		array $dependencies = [],
		$version = '',
		$media = 'all'
	) {

		$url = DebugAwareAssetURL::from_location( $location );

		if ( null !== $version ) {
			$version = $version ?: $url->version();
		}

		return new static(
			$handle,
			(string) $url,
			$dependencies,
			$version,
			$media
		);
	}

	/**
	 * Returns the dependencies.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The dependencies.
	 */
	public function dependencies() {

		return $this->dependencies;
	}

	/**
	 * Returns the handle.
	 *
	 * @since 3.0.0
	 *
	 * @return string The handle.
	 */
	public function handle() {

		return $this->handle;
	}

	/**
	 * Returns the file URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string The file URL.
	 */
	public function url() {

		return $this->url;
	}

	/**
	 * Returns the file version.
	 *
	 * @since 3.0.0
	 *
	 * @return string|null The file version.
	 */
	public function version() {

		return $this->version;
	}

	/**
	 * Returns the handle.
	 *
	 * @since 3.0.0
	 *
	 * @return string The handle.
	 */
	public function __toString() {

		return $this->handle;
	}

	/**
	 * Adds the given conditional to the style.
	 *
	 * @since 3.0.0
	 *
	 * @param string $conditional Conditional string.
	 *
	 * @return static Style instance.
	 */
	public function add_conditional( $conditional ) {

		wp_style_add_data( $this->handle, 'conditional', (string) $conditional );

		return $this;
	}

	/**
	 * Returns the style media data.
	 *
	 * @since 3.0.0
	 *
	 * @return string The style media data.
	 */
	public function media() {

		return $this->media;
	}
}
