<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

use function Inpsyde\MultilingualPress\nonce_field;

/**
 * Translation completed setting view.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
class TranslationCompletedSettingView {

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var PostsRepository
	 */
	private $posts_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PostsRepository $posts_repository  Untranslated posts repository object.
	 * @param Nonce           $nonce             Nonce object.
	 * @param ActivePostTypes $active_post_types Active post types storage object.
	 */
	public function __construct( PostsRepository $posts_repository, Nonce $nonce, ActivePostTypes $active_post_types ) {

		$this->posts_repository = $posts_repository;

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

		$post_id = (int) $post->ID;

		$translated = $this->posts_repository->is_post_translated( $post_id );

		/**
		 * Filters whether or not the checkbox for the current post should be rendered.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $show_checkbox Whether or not to show the checkbox.
		 * @param int  $post_id       Post ID.
		 * @param bool $translated    Whether or not the post is translated.
		 */
		if ( ! apply_filters( 'multilingualpress.show_translation_completed_checkbox', true, $post_id, $translated ) ) {
			return;
		}

		$id = 'mlp-translation-completed';
		?>
		<div class="misc-pub-section misc-pub-mlp-translation-completed">
			<?php echo nonce_field( $this->nonce ); ?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( PostsRepository::META_KEY ); ?>"
					value="1" id="<?php echo esc_attr( $id ); ?>" <?php checked( $translated ); ?>>
				<?php _e( 'Translation completed', 'multilingualpress' ); ?>
			</label>
		</div>
		<?php
	}
}
