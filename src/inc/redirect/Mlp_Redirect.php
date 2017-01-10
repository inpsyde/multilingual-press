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

		if ( ! is_admin() ) {
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				( new Mlp_Redirect_Frontend(
					new Mlp_Redirect_Response(
						new Mlp_Language_Negotiation( $this->translations )
					),
					'inpsyde_multilingual_redirect'
				) )->setup();
			}
		}
	}
}
