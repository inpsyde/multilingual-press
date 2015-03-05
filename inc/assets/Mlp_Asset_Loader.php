<?php
/**
 * Enqueues scripts and stylesheets.
 *
 * @version 2014.10.09
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Asset_Loader {

	/**
	 * @type array
	 */
	private $handles;

	/**
	 * @param array $handles
	 */
	public function __construct( Array $handles ) {

		$this->handles = $handles;
	}

	/**
	 * Called by Mlp_Assets::provide() on one of the enqueue actions
	 *
	 * @see    Mlp_Assets::provide()
	 * @return void
	 */
	public function enqueue() {

		foreach ( $this->handles as $handle => $extension ) {
			if ( 'css' === $extension )
				wp_enqueue_style( $handle );
			else
				wp_enqueue_script( $handle );
		}
	}
}