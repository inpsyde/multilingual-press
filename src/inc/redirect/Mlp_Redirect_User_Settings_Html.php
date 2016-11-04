<?php

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSettingViewModel;

/**
 * Show user option to disable the language redirect.
 *
 * @version 2014.07.05
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Redirect_User_Settings_Html implements UserSettingViewModel {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @param string $key
	 * @param Nonce  $nonce Nonce object.
	 */
	public function __construct( $key, Nonce $nonce ) {

		$this->key = $key;

		$this->nonce = $nonce;
	}

	/**
	 * Content of 'th'.
	 *
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public function title( WP_User $user ) {

		return esc_html__( 'Language redirect', 'multilingual-press' );
	}

	/**
	 * Content of 'td'.
	 *
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public function markup( WP_User $user ) {

		$id = "{$this->key}_id";

		ob_start();
		?>
		<label for="<?php echo esc_attr( $id ); ?>">
			<input type="checkbox" name="<?php echo esc_attr( $this->key ); ?>" value="1"
				id="<?php echo esc_attr( $id ); ?>"
				<?php checked( 1, (int) get_user_meta( $user->ID, $this->key ) ); ?>>
			<?php
			esc_html_e(
				'Do not redirect me automatically to the best matching language version.',
				'multilingual-press'
			);
			echo \Inpsyde\MultilingualPress\nonce_field( $this->nonce );
			?>
		</label>
		<?php

		return ob_get_clean();
	}
}
