<?php
/**
 * Manage plugin/theme paths and URLs
 *
 * @version 2014.10.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */

class Mlp_Internal_Locations implements Mlp_Locations_Interface {

	/**
	 * @type array
	 */
	private $directories = array();

	/**
	 * @param  string $path
	 * @param  string $url
	 * @param  string $identifier
	 * @return void
	 */
	public function add_dir( $path, $url, $identifier = '' ) {

		if ( '' === $identifier ) {
			$identifier = basename( $path ); // is without slashes already
		}

		$this->directories[ $identifier ]['path'] = rtrim( $path, '/' );
		$this->directories[ $identifier ]['url']  = rtrim( $url, '/' ) . '/';
	}

	/**
	 * @param  string $identifier
	 * @param  string $type
	 * @return string
	 */
	public function get_dir( $identifier, $type ) {

		if ( isset( $this->directories[ $identifier ][ $type ] ) ) {
			return $this->directories[ $identifier ][ $type ];
		}

		return '';
	}

	/**
	 * Check if a directory type is registered
	 *
	 * @param  string $identifier
	 * @return bool
	 */
	public function has_dir( $identifier ) {

		return ! empty( $this->directories[ $identifier ] );
	}
}
