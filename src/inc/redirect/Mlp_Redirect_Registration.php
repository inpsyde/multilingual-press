<?php
/**
 * Handles the registration of the redirect feature.
 *
 * @version 2014.04.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
class Mlp_Redirect_Registration {

	/**
	 * @var Mlp_Module_Manager_Interface
	 */
	private $modules;

	/**
	 * Constructor
	 *
	 * @param Mlp_Module_Manager_Interface $modules
	 */
	public function __construct( Mlp_Module_Manager_Interface $modules ) {
		$this->modules   = $modules;
	}

	/**
	 * Register the feature.
	 *
	 * @return bool Feature is active or not.
	 */
	public function setup() {

		$desc = __(
			'Redirect visitors according to browser language settings.',
			'multilingual-press'
		);

		$settings = array (
			'display_name'	=> __( 'HTTP Redirect', 'multilingual-press' ),
			'slug'			=> 'class-' . __CLASS__,
			'description'   => $desc
		);

		return $this->modules->register( $settings );
	}
}
