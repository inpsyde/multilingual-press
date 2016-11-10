<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts;

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use WP_Post;

/**
 * Translation completed setting view.
 *
 * @package Inpsyde\MultilingualPress\Widget\Dashboard\UntranslatedPosts
 * @since   3.0.0
 */
class TranslationCompletedSettingView {

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var PostRepository
	 */
	private $post_repository;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param PostRepository $post_repository Untranslated posts repository object.
	 * @param Nonce          $nonce           Nonce object.
	 */
	public function __construct( PostRepository $post_repository, Nonce $nonce ) {

		$this->post_repository = $post_repository;

		$this->nonce = $nonce;
	}

	/**
	 * Renders the setting markup.
	 *
	 * @since   3.0.0
	 * @wp-hook post_submitbox_misc_actions
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function render( WP_Post $post ) {

		$post_id = $post->ID;

		$translated = $this->post_repository->is_post_translated( $post_id );

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
			<?php echo \Inpsyde\MultilingualPress\nonce_field( $this->nonce ); ?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( PostRepository::META_KEY ); ?>"
					value="1" id="<?php echo esc_attr( $id ); ?>" <?php checked( $translated ); ?>>
				<?php _e( 'Translation completed', 'multilingual-press' ); ?>
			</label>
		</div>
		<?php
	}
}
