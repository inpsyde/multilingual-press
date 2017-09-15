<?php
/**
 * Tiny framework for extra user settings.
 *
 * @version 2014.07.04
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_User_Settings_Controller {

	/**
	 * @var Mlp_User_Settings_View_Interface
	 */
	private $view;

	/**
	 * @var Mlp_User_Settings_Updater_Interface
	 */
	private $updater;

	/**
	 * @param Mlp_User_Settings_View_Interface    $view
	 * @param Mlp_User_Settings_Updater_Interface $updater
	 */
	public function __construct(
		Mlp_User_Settings_View_Interface $view,
		Mlp_User_Settings_Updater_Interface $updater
	) {

		$this->view    = $view;
		$this->updater = $updater;
	}

	/**
	 * Register callbacks.
	 *
	 * @return void
	 */
	public function setup() {

		$container = new Mlp_User_Settings_Container_Html( $this->view );

		add_action( 'personal_options', array( $container, 'render' ) );
		add_action( 'profile_update',   array( $this->updater, 'save' ) );
	}
}
