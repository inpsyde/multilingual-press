<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Setting\User\UserSettingViewModel;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Redirect user setting.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect
 * @since   3.0.0
 */
final class RedirectUserSetting implements UserSettingViewModel {

	/**
	 * @var string
	 */
	private $meta_key;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string             $meta_key   User meta key.
	 * @param Nonce              $nonce      Nonce object.
	 * @param SettingsRepository $repository Settings repository object.
	 */
	public function __construct( string $meta_key, Nonce $nonce, SettingsRepository $repository ) {

		$this->meta_key = $meta_key;

		$this->nonce = $nonce;

		$this->repository = $repository;
	}

	/**
	 * Renders the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_User $user User object.
	 *
	 * @return void
	 */
	public function render( \WP_User $user ) {

		?>
		<label for="<?php echo esc_attr( $this->meta_key ); ?>">
			<input type="checkbox" name="<?php echo esc_attr( $this->meta_key ); ?>" value="1"
				id="<?php echo esc_attr( $this->meta_key ); ?>"
				<?php checked( $this->repository->get_user_setting( (int) $user->ID ) ); ?>>
			<?php esc_html_e( 'Do not redirect me to the best matching language version.', 'multilingualpress' ); ?>
		</label>
		<?php
		nonce_field( $this->nonce );
	}

	/**
	 * Returns the title of the user setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the user setting.
	 */
	public function title(): string {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Redirect', 'multilingualpress' ),
			esc_attr( $this->meta_key )
		);
	}
}
