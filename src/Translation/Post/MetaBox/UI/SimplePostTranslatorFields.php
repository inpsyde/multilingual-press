<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
class SimplePostTranslatorFields {

	const TRANSLATABLE_FIELD = 'mlp_to_translate';

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	public function render_top_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		if ( $remote_post ) {
			$this->render_title_input( $source_post, $remote_post );
		} else {
			$this->render_translatable_input( $remote_site_id );
		}
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	public function render_main_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		if ( $remote_post ) {
			$this->render_editor_input( $source_post, $remote_post );
		}
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	public function render_bottom_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

	}

	/**
	 * @param \WP_Post $source_post
	 * @param \WP_Post $remote_post
	 *
	 * @return void
	 */
	private function render_title_input( \WP_Post $source_post, \WP_Post $remote_post ) {

		if ( ! post_type_supports( $source_post->post_type, 'title' ) ) {
			return;
		}

		$title = trim( $remote_post->post_title );
		if ( ! $title ) {
			return;
		}

		echo '<p><strong>' . esc_html( $title ) . '</strong></p>';
	}

	/**
	 * @param int $remote_site_id
	 *
	 * @return void
	 */
	private function render_translatable_input( int $remote_site_id ) {

		$id = self::TRANSLATABLE_FIELD . "-{$remote_site_id}";
		?>
		<p>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input
					type="checkbox"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( self::TRANSLATABLE_FIELD ); ?>[]"
					value="<?php echo esc_attr( $remote_site_id ); ?>" />
				<?php esc_html_e( 'Translate this post', 'multilingualpress' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * @param \WP_Post $source_post
	 * @param \WP_Post $remote_post
	 *
	 * @return void
	 */
	private function render_editor_input( \WP_Post $source_post, \WP_Post $remote_post ) {

		if ( ! post_type_supports( $source_post->post_type, 'editor' ) ) {
			return;
		}

		$post_content = $remote_post->post_content;

		printf(
			'<textarea class="large-text" cols="80" rows="%1$d" placeholder="%2$s" readonly>%3$s</textarea>',
			esc_attr( min( substr_count( $post_content, "\n" ) + 1, 10 ) ),
			esc_attr_x( 'No content yet.', 'placeholder for empty translation textarea', 'multilingualpress' ),
			esc_textarea( $post_content )
		);
	}
}
