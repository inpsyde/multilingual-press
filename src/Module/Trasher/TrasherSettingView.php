<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Trasher setting view.
 *
 * @package Inpsyde\MultilingualPress\Module\Trasher
 * @since   3.0.0
 */
class TrasherSettingView {

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

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
	 * @param TrasherSettingRepository  $setting_repository Trasher setting repository object.
	 * @param Nonce                     $nonce              Nonce object.
     * @param ActivePostTypes           $active_post_types  Active post types storage object.
	 */
	public function __construct(
        TrasherSettingRepository $setting_repository,
        Nonce $nonce,
        ActivePostTypes $active_post_types
    ) {

		$this->setting_repository = $setting_repository;

		$this->nonce = $nonce;

		$this->active_post_types = $active_post_types;
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

		if ( ! $this->active_post_types->includes( (string) $post->post_type ) ) {
			return;
		}

		$id = 'mlp-trasher';
		?>
		<div class="misc-pub-section misc-pub-mlp-trasher">
			<?php echo nonce_field( $this->nonce ); ?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( TrasherSettingRepository::META_KEY ); ?>"
					value="1" id="<?php echo esc_attr( $id ); ?>"
					<?php checked( $this->setting_repository->get_setting( (int) $post->ID ) ); ?>>
				<?php _e( 'Send all the translations to trash when this post is trashed.', 'multilingualpress' ); ?>
			</label>
		</div>
		<?php
	}
}
