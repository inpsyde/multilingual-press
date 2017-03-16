<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Asset URL data type implementation aware of debug mode and thus potentially minified asset files.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
final class DebugAwareAssetURL implements AssetURL {

	/**
	 * @var string
	 */
	private $url = '';

	/**
	 * @var string
	 */
	private $version = '';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file     File name (e.g., admin.css).
	 * @param string $dir_path Local path to the directory containing the file.
	 * @param string $dir_url  Public URL for the directory containing the file.
	 */
	public function __construct( string $file, string $dir_path, string $dir_url ) {

		$dir_path = rtrim( $dir_path, '/' );

		$file = $this->get_file( $file, $dir_path );

		$file_path = "$dir_path/$file";
		if ( is_readable( $file_path ) ) {
			$this->url = rtrim( $dir_url, '/' ) . "/$file";

			$this->version = filemtime( $file_path );
		}
	}

	/**
	 * Returns a new URL object, instantiated according to the given location object.
	 *
	 * @since 3.0.0
	 *
	 * @param AssetLocation $location Asset location object.
	 *
	 * @return DebugAwareAssetURL URL object.
	 */
	public static function from_location( AssetLocation $location ): DebugAwareAssetURL {

		return new static( $location->file(), $location->path(), $location->url() );
	}

	/**
	 * Returns the URL string.
	 *
	 * @since 3.0.0
	 *
	 * @return string URL string.
	 */
	public function __toString(): string {

		return $this->url;
	}

	/**
	 * Returns the file version.
	 *
	 * @since 3.0.0
	 *
	 * @return string File version.
	 */
	public function version(): string {

		return $this->version;
	}

	/**
	 * Returns the name of the minified version of the given file if it exists and not debugging, otherwise the
	 * unmodified file.
	 *
	 * @param string $file     File name (e.g., admin.css).
	 * @param string $dir_path Local path to the directory containing the file.
	 *
	 * @return string Name of the minified or unmodified file, depending on debugging settings.
	 */
	private function get_file( string $file, string $dir_path ): string {

		if ( \Inpsyde\MultilingualPress\is_script_debug_mode() ) {
			return $file;
		}

		$minified_file = $this->get_minified_file( $file );

		if ( $minified_file === $file ) {
			return $file;
		}

		if ( is_readable( "$dir_path/$minified_file" ) ) {
			return $minified_file;
		}

		return $file;
	}

	/**
	 * Returns the given file with inserted ".min" infix, if not already minified file.
	 *
	 * @param string $file Normal file name (e.g., admin.css).
	 *
	 * @return string Minified file.
	 */
	private function get_minified_file( string $file ): string {

		// Check for already minified file.
		if ( preg_match( '~\.min\.[^.]+$~', $file ) ) {
			return $file;
		}

		return preg_replace( '~\.[^.]+$~', '.min$0', $file );
	}
}
