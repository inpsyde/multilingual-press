<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\API\Translations;

/**
 * Main controller for the Redirect feature.
 */
class Mlp_Redirect {

	/**
	 * @var Translations
	 */
	private $translations;

	/**
	 * @var string
	 */
	private $option = 'inpsyde_multilingual_redirect';

	/**
	 * Constructor.
	 *
	 * @param Translations $translations
	 */
	public function __construct( Translations $translations ) {

		$this->translations = $translations;
	}

	/**
	 * Determines the current state and actions, and calls subsequent methods.
	 *
	 * @return void
	 */
	public function setup() {

		( new Mlp_Redirect_User_Settings() )->setup();

		if ( is_admin() ) {
			if ( is_network_admin() ) {
				( new Mlp_Redirect_Site_Settings( $this->option ) )->setup();
			}
		} else {
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				( new Mlp_Redirect_Frontend(
					new Mlp_Redirect_Response(
						new Mlp_Language_Negotiation( $this->translations )
					),
					$this->option
				) )->setup();
			}
		}
	}
}
