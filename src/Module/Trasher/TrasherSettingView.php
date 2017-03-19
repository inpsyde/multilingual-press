<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Trasher setting view.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
 * @since   3.0.0
 */
class TrasherSettingView {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var TrasherSettingRepository
	 */
	private $setting_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param TrasherSettingRepository $setting_repository Trasher setting repository object.
	 * @param Nonce                    $nonce              Nonce object.
	 */
	public function __construct( TrasherSettingRepository $setting_repository, Nonce $nonce ) {

		$this->setting_repository = $setting_repository;

		$this->nonce = $nonce;
	}

	/**
	 * Renders the setting markup.
	 *
	 * @since   3.0.0
	 * @wp-hook post_submitbox_misc_actions
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function render( \WP_Post $post ) {

		$id = 'mlp-trasher';
		?>
		<div class="misc-pub-section misc-pub-mlp-trasher">
			<?php echo nonce_field( $this->nonce ); ?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( TrasherSettingRepository::META_KEY ); ?>"
					value="1" id="<?php echo esc_attr( $id ); ?>"
					<?php checked( $this->setting_repository->get_setting( (int) $post->ID ) ); ?>>
				<?php _e( 'Send all the translations to trash when this post is trashed.', 'multilingual-press' ); ?>
			</label>
		</div>
		<?php
	}
}
