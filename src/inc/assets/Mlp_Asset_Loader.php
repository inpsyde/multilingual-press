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
	 * @var array
	 */
	private $handles;

	/**
	 * @var array
	 */
	private $l10n;

	/**
	 * Constructor. Set up the properties.
	 *
	 * @param array $handles One or more asset handles.
	 * @param array $l10n    Optional. Localized data. Defaults to array().
	 */
	public function __construct( array $handles, array $l10n = array() ) {

		$this->handles = $handles;
		$this->l10n = $l10n;
	}

	/**
	 * Called for one of the enqueue actions.
	 *
	 * @see Mlp_Assets::provide()
	 *
	 * @return void
	 */
	public function enqueue() {

		foreach ( $this->handles as $handle => $extension ) {
			if ( 'css' === $extension ) {
				wp_enqueue_style( $handle );
			} else {
				wp_enqueue_script( $handle );
				if ( ! empty( $this->l10n[ $handle ] ) ) {
					foreach ( $this->l10n[ $handle ] as $object => $data ) {
						wp_localize_script( $handle, $object, $data );
					}
				}
			}
		}
	}

}
