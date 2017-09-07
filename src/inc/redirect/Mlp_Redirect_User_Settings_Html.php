<?php
/**
 * Show user option to disable the language redirect.
 *
 * @version 2014.07.05
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Redirect_User_Settings_Html implements Mlp_User_Settings_View_Interface {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * @param string                            $key
	 * @param Inpsyde_Nonce_Validator_Interface $nonce
	 */
	public function __construct( $key, Inpsyde_Nonce_Validator_Interface $nonce ) {

		$this->key   = $key;
		$this->nonce = $nonce;
	}

	/**
	 * Content of 'th'.
	 *
	 * @param WP_User $user
	 * @return void
	 */
	public function show_header( WP_User $user ) {
		esc_html_e( 'Language redirect', 'multilingual-press' );
	}

	/**
	 * Content of 'td'.
	 *
	 * @param WP_User $user
	 * @return void
	 */
	public function show_content( WP_User $user ) {

		$current = (int) get_user_meta( $user->ID, $this->key );
		$check   = checked( 1, $current, false );
		$text    = __(
			'Do not redirect me automatically to the best matching language version.',
			'multilingual-press'
		);

		wp_nonce_field( $this->nonce->get_action(), $this->nonce->get_name() );
		printf(
			'<label for="%1$s_id">
				<input type="checkbox" value="1" name="%1$s" id="%1$s_id" %2$s>
				%3$s
			</label>',
			esc_attr( $this->key ),
			esc_attr( $check ),
			esc_html( $text )
		);
	}
}
