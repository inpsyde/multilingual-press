<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Debug-aware script data type implementation.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
final class DebugAwareScript implements Script {

	/**
	 * @var array[]
	 */
	private $data = [];

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
	private $url;

	/**
	 * @var string|null
	 */
	private $version;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string      $handle       The handle.
	 * @param string      $url          The public URL for the directory containing the file.
	 * @param string[]    $dependencies Optional. The dependencies. Defaults to empty array.
	 * @param string|null $version      Optional. Version of the file. Defaults to null.
	 */
	public function __construct(
		string $handle,
		string $url,
		array $dependencies = [],
		$version = null
	) {

		$this->handle = $handle;

		$this->url = $url;

		$this->dependencies = array_map( 'strval', $dependencies );

		if ( null !== $version ) {
			$this->version = $version;
		}
	}

	/**
	 * Returns a new script object, instantiated according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string        $handle       The handle.
	 * @param AssetLocation $location     Location object.
	 * @param string[]      $dependencies Optional. The dependencies. Defaults to empty array.
	 * @param string|null   $version      Optional. Version of the file. Defaults to null.
	 *
	 * @return Script Script object.
	 */
	public static function from_location(
		string $handle,
		AssetLocation $location,
		array $dependencies = [],
		$version = null
	): Script {

		$url = DebugAwareAssetURL::from_location( $location );

		if ( null !== $version ) {
			$version = $version ?: $url->version();
		}

		return new static(
			$handle,
			(string) $url,
			$dependencies,
			$version
		);
	}

	/**
	 * Returns the dependencies.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The dependencies.
	 */
	public function dependencies(): array {

		return $this->dependencies;
	}

	/**
	 * Returns the handle.
	 *
	 * @since 3.0.0
	 *
	 * @return string The handle.
	 */
	public function handle(): string {

		return $this->handle;
	}

	/**
	 * Returns the file URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string The file URL.
	 */
	public function url(): string {

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
	public function __toString(): string {

		return $this->handle;
	}

	/**
	 * Makes the given data available for the script.
	 *
	 * @since 3.0.0
	 *
	 * @param string $object_name The name of the JavaScript variable holding the data.
	 * @param array  $data        The data to be made available for the script.
	 *
	 * @return Script Script instance.
	 */
	public function add_data( string $object_name, array $data ): Script {

		$this->data[ $object_name ] = $data;

		return $this;
	}

	/**
	 * Clears the data so it won't be output another time.
	 *
	 * @since 3.0.0
	 *
	 * @return Script Script instance.
	 */
	public function clear_data(): Script {

		$this->data = [];

		return $this;
	}

	/**
	 * Returns all data to be made available for the script.
	 *
	 * @since 3.0.0
	 *
	 * @return array[] Data to be made available for the script.
	 */
	public function data(): array {

		return $this->data;
	}
}
