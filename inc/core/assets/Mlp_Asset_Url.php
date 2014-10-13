<?php
/**
 * URLs for scripts and stylesheets, minified if possible.
 *
 * @version 2014.10.07
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */


class Mlp_Asset_Url implements Mlp_Asset_Url_Interface {

	/**
	 * @type string
	 */
	private $url = '';

	/**
	 * File version
	 *
	 * @type string
	 */
	private $version = '';

	/**
	 * Constructor
	 *
	 * @param string $file_name The normal name like 'backend.css'
	 * @param string $dir_path  Local path to the directory with the file.
	 * @param string $dir_url   Public URL for $dir_path.
	 */
	public function __construct( $file_name, $dir_path, $dir_url ) {

		$this->url = $this->build_url( $file_name, $dir_path, $dir_url );
	}

	/**
	 * Returns an URL
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->url;
	}

	/**
	 * @return string
	 */
	public function get_version() {

		return $this->version;
	}

	/**
	 * Check path and minify
	 *
	 * @see Mlp_Asset_Url::__construct()
	 * @param  string $file_name
	 * @param  string $dir_path
	 * @param  string $dir_url
	 * @return string
	 */
	private function build_url( $file_name, $dir_path, $dir_url ) {

		$dir_path  = rtrim( $dir_path, '/' );
		$dir_url  = rtrim( $dir_url, '/' );
		$file_name = $this->maybe_minify( $file_name, $dir_path );
		$file_path = "$dir_path/$file_name";

		if ( ! is_readable( $file_path ) )
			return '';

		$this->version = filemtime( $file_path );

		return "$dir_url/$file_name";
	}

	/**
	 * Minify if debug mode is on and minified file exists
	 *
	 * @param  string $file_name
	 * @param  string $dir_path
	 * @return string
	 */
	private function maybe_minify( $file_name, $dir_path ) {

		// We do not minify in debug mode
		if ( $this->is_debug_mode() )
			return $file_name;

		$minified_file_name = $this->get_minified_file_name( $file_name );

		if ( $minified_file_name === $file_name )
			return $file_name;

		if ( ! is_readable( "$dir_path/$minified_file_name" ) )
			return $file_name;;

		return $minified_file_name;
	}

	/**
	 * @return bool
	 */
	private function is_debug_mode() {

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			return TRUE;

		return defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG;
	}

	/**
	 * Add '.min.' to file name
	 *
	 * @param  string $file_name
	 * @return string
	 */
	private function get_minified_file_name( $file_name ) {

		// This is already a minified file.
		if ( FALSE !== strpos( $file_name, '.min.' ) )
			return $file_name;

		// The file might have a name like 'plugin.admin.network.css'
		$parts     = explode( '.', $file_name );
		$extension = array_pop( $parts );

		return join( '.', $parts ) . ".min.$extension";
	}
}