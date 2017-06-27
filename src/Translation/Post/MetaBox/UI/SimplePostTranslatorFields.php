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
	 * @return string
	 */
	public function top_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ): string {

		$output = $remote_post
			? $this->title_input( $remote_post )
			: $this->translatable_input( $remote_site_id );

		return $output;
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return string
	 */
	public function main_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ): string {

		$output =  $remote_post
			? $this->editor_input( $remote_post )
			: '';

		return $output;
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return string
	 */
	public function bottom_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ): string {

		return '';
	}

	/**
	 * @param \WP_Post $remote_post
	 *
	 * @return string
	 */
	private function title_input( \WP_Post $remote_post ): string {

		return '<h2 class="headline" style="margin: 0;">' . esc_html( $remote_post->post_title ) . '</h2>';
	}

	/**
	 * @param int $remote_site_id
	 *
	 * @return string
	 */
	private function translatable_input( int $remote_site_id ): string {

		$id = self::TRANSLATABLE_FIELD . "-{$remote_site_id}";

		ob_start();
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

		return ob_get_clean();
	}

	/**
	 * @param $remote_post
	 *
	 * @return string
	 */
	private function editor_input( $remote_post ): string {

		$post_content = $remote_post->post_content;

		return sprintf(
			'<textarea class="large-text" cols="80" rows="%1$d" placeholder="%2$s" readonly>%3$s</textarea>',
			min( substr_count( $post_content, "\n" ) + 1, 10 ),
			esc_attr_x( 'No content yet.', 'placeholder for empty translation textarea', 'multilingualpress' ),
			esc_textarea( $post_content )
		);
	}
}
