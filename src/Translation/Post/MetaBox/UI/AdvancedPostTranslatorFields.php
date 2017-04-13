<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use function Inpsyde\MultilingualPress\get_post_taxonomies_with_terms;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
class AdvancedPostTranslatorFields {

	const SOURCE_POST_ID = 'source_post_id';

	const REMOTE_POST_ID = 'remote_post_id';

	const POST_TITLE = 'title';

	const POST_NAME = 'name';

	const POST_CONTENT = 'content';

	const POST_EXCERPT = 'excerpt';

	const COPIED_POST = 'copied_post';

	const SYNC_THUMBNAIL = 'sync_thumb';

	const TAXONOMY = 'tax';

	/**
	 * Constructor. Sets properties.
	 */
	public function __construct() {

		$this->input_helper = new PostTranslatorInputHelper();

		$this->current_site_id = (int) get_current_blog_id();
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Post      $source_post
	 * @param \WP_Post|null $post
	 *
	 * @return string
	 */
	public function top_fields( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		$output = $this->posts_id_input( $remote_site_id, $source_post, $post );
		$output .= $this->title_input( $remote_site_id, $source_post, $post );
		$output .= $this->name_input( $remote_site_id, $source_post );
		$output .= $this->editor( $remote_site_id, $source_post, $post );

		return $output;
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Post      $source_post
	 * @param \WP_Post|null $post
	 *
	 * @return string
	 */
	public function main_fields( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		$output = $this->editor( $remote_site_id, $source_post, $post );
		$output .= $this->excerpt_input( $remote_site_id, $source_post, $post );
		$output .= $this->sync_thumbnail_input( $remote_site_id );

		return $output;

	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Post      $source_post
	 * @param \WP_Post|null $post
	 *
	 * @return string
	 */
	public function bottom_fields( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		return $this->taxonomies_input( $remote_site_id, $source_post, $post );
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Post      $source_post
	 * @param \WP_Post|null $post
	 *
	 * @return string
	 */
	private function posts_id_input( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		$source_name = $this->input_helper->field_name( $remote_site_id, self::SOURCE_POST_ID );
		$remote_name = $this->input_helper->field_name( $remote_site_id, self::REMOTE_POST_ID );

		$remote_pid = $post ? (int) $post->ID : 0;

		$output = sprintf( '<input type="hidden" name="%s" value="%d">', $source_name, (int) $source_post->ID );
		$output .= sprintf( '<input type="hidden" name="%s" value="%d">', $remote_name, $remote_pid );

		return $output;
	}

	/**
	 * @param int           $remote_site_id
	 * @param \WP_Post      $source_post
	 * @param \WP_Post|null $post
	 *
	 * @return string
	 */
	private function title_input( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		if ( ! post_type_supports( $source_post->post_type, 'title' ) ) {
			return '';
		}

		$placeholder = __( 'Enter title here', 'multilingualpress' );

		/** This filter is documented in wp-admin/edit-form-advanced.php */
		$placeholder = apply_filters( 'enter_title_here', $placeholder, $source_post );

		ob_start();
		?>
		<div class="mlp-titlediv">
			<div>
				<input
					type="text"
					name="<?php echo $this->input_helper->field_name( $remote_site_id, self::POST_TITLE ); ?>"
					value="<?php echo $post ? esc_attr( $post->post_title ) : ''; ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					size="30"
					class="mlp-title"
					id="<?php echo $this->input_helper->field_id( $remote_site_id, 'title' ); ?>">
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param int      $remote_site_id
	 * @param \WP_Post $post Remote post
	 *
	 * @return string
	 */
	private function name_input( int $remote_site_id, \WP_Post $post = null ): string {

		$id = $this->input_helper->field_id( $remote_site_id, 'name' );

		$value = $post ? $post->post_name : '';
		if ( ! $value && $post && $post->post_title ) {
			$value = sanitize_title( $post->post_title );
		}

		ob_start();
		?>
		<div class="mlp-namediv">
			<div>
				<label for="<?php echo $id; ?>">
					<?php _e( 'Post Name:', 'multilingualpress' ) ?><br>
					<input
						type="text"
						name="<?php echo $this->input_helper->field_name( $remote_site_id, self::POST_NAME ); ?>"
						value="<?php echo esc_attr( urldecode( $value ) ); ?>"
						placeholder="<?php echo esc_attr__( 'Enter name here', 'multilingualpress' ) ?>"
						size="30"
						class="mlp-name"
						id="<?php echo $id; ?>">
				</label>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param int      $remote_site_id
	 * @param \WP_Post $source_post
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function editor( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		if ( ! post_type_supports( $source_post->post_type, 'editor' ) ) {
			return '';
		}

		$editor_id = $this->input_helper->field_id( $remote_site_id, 'content' );

		$content = $post ? $post->post_content : '';

		$copy_button = $this->copy_button( $editor_id, $remote_site_id );

		ob_start();
		wp_editor( $content, $editor_id, [
			'tabindex'      => false,
			'editor_height' => 150,
			'resize'        => true,
			'textarea_name' => $this->input_helper->field_name( $remote_site_id, self::POST_CONTENT ),
			'media_buttons' => false,
			'tinymce'       => [ 'resize' => true, ],
		] );

		return "{$copy_button}\n" . ob_get_clean();
	}

	/**
	 * @param int      $remote_site_id
	 * @param \WP_Post $source_post
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function excerpt_input( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		if ( ! post_type_supports( $source_post->post_type, 'excerpt' ) ) {
			return '';
		}

		$id = $this->input_helper->field_id( $remote_site_id, 'excerpt' );

		$value = $post ? esc_textarea( $post->post_excerpt ) : '';

		ob_start();
		?>
		<div class="mlp-excerptdiv">
			<div>
				<label for="<?php echo $id; ?>"><?php _e( 'Post Excerpt:', 'multilingualpress' ) ?></label>
				<textarea
					name="<?php echo $this->input_helper->field_name( $remote_site_id, self::POST_EXCERPT ); ?>"
					placeholder="<?php echo esc_attr__( 'Enter excerpt here', 'multilingualpress' ) ?>"
					class="mlp-excerpt"
					id="<?php echo $id; ?>"><?php echo $value; ?></textarea>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param int $remote_site_id
	 *
	 * @return string
	 */
	private function sync_thumbnail_input( int $remote_site_id ): string {

		$id = $this->input_helper->field_id( $remote_site_id, 'thumbnail' );

		ob_start();
		?>
		<p>
			<label for="<?php echo $id; ?>_id">
				<input
					type="checkbox"
					name="<?php echo $this->input_helper->field_name( $remote_site_id, self::SYNC_THUMBNAIL ); ?>"
					value="1"
					id="<?php echo $id; ?>_id">
				<?php _e( 'Copy the featured image of the source post.', 'multilingualpress' ); ?>
				<span class="description">
					<?php _e( 'Overwrites an existing featured image in the target post.', 'multilingualpress' ); ?>
				</span>
			</label>
		</p>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param int      $remote_site_id
	 * @param \WP_Post $source_post
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function taxonomies_input( int $remote_site_id, \WP_Post $source_post, \WP_Post $post = null ): string {

		switch_to_blog( $remote_site_id );
		$taxonomies = $post ? get_post_taxonomies_with_terms( $post ) : get_post_taxonomies_with_terms( $source_post );
		restore_current_blog();

		if ( ! $taxonomies ) {
			return '';
		}

		/**
		 * Filter mutually exclusive taxonomies.
		 *
		 * @param string[] $taxonomies Mutually exclusive taxonomy names.
		 */
		$exclusive_tax = apply_filters( 'mlp_mutually_exclusive_taxonomies', [ 'post_format' ] );

		$toggle_id = esc_attr( 'tax_toggle_' . $remote_site_id );

		ob_start();
		?>
		<button
			type="button"
			name="toggle_<?php echo esc_attr( $remote_site_id ); ?>"
			data-toggle-target="#<?php echo $toggle_id; ?>"
			class="button secondary mlp-click-toggler">
			<?php echo esc_html__( 'Change taxonomies', 'multilingualpress' ); ?>
		</button>
		<div class="hidden" id="<?php echo $toggle_id; ?>">
			<?php if ( ! empty( $taxonomies['inclusive'] ) ) : ?>
				<div class="mlp-taxonomy-fieldset-container">
					<?php
					foreach ( $taxonomies as $taxonomy => $taxonomy_data ) {

						$input_type = in_array( $taxonomy, $exclusive_tax, true ) ? 'radio' : 'checkbox';

						if ( $taxonomy_data->terms ) {
							$this->show_terms_input( $taxonomy_data, $remote_site_id, $input_type, $post );
						}
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param string $editor_id      tinyMCE editor ID.
	 * @param int    $remote_site_id Remote site ID.
	 *
	 * @return string
	 */
	private function copy_button( $editor_id, int $remote_site_id ): string {

		$matches = [];

		preg_match( '~mlp-translation-data-(\d+)-content~', $editor_id, $matches );
		if ( empty( $matches[1] ) ) {
			return '';
		}

		$name = $this->input_helper->field_name( $remote_site_id, self::COPIED_POST );

		$id = $this->input_helper->field_id( $remote_site_id, 'copied-post' );

		ob_start();
		?>
		<div class="wp-media-buttons">
			<button class="button mlp-copy-post-button" data-site-id="<?php echo esc_attr( $matches[1] ); ?>">
				<span class="wp-media-buttons-icon"></span>
				<?php esc_html_e( 'Copy source post', 'multilingualpress' ); ?>
			</button>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="" id="<?php echo esc_attr( $id ); ?>">
		<?php

		return ob_get_clean();
	}

	/**
	 * @param \stdClass     $taxonomy_data
	 * @param int           $remote_site_id
	 * @param string        $input_type Either 'checkbox' (e.g., for categories) or 'radio' (e.g., for post formats).
	 *
	 * @param \WP_Post|null $post
	 *
	 * @return string
	 */
	private function show_terms_input(
		\stdClass $taxonomy_data,
		int $remote_site_id,
		string $input_type,
		\WP_Post $post = null
	): string {

		$name = $this->input_helper->field_name( $remote_site_id, self::TAXONOMY ) . "[{$taxonomy_data->object->name}]";

		$inputs_markup = '';
		$inputs_format = '<label for="%2$s">';
		$inputs_format .= '<input type="%3$s" name="%4$s[]" id="%2$s" value="%5$s"%6$s> %1$s';
		$inputs_format .= '</label><br>';

		/** @var \stdClass $term_data */
		foreach ( $taxonomy_data->terms as $term_data ) {

			/** @var \WP_Term $term */
			$term = $term_data->term;

			// If post is null, $term_data->assigned refers to source post, so never consider it assigned on remote.
			$assigned = $post ? checked( $term_data->assigned, true, false ) : '';

			$inputs_markup .= sprintf(
				$inputs_format,
				esc_html( $term->name ),
				esc_attr( "term-{$remote_site_id}-{$term->term_taxonomy_id}" ),
				$input_type,
				$name,
				esc_attr( $term->term_id ),
				$assigned
			);
		}

		$fieldset_format = '<fieldset class="mlp-taxonomy-box"><legend>%s</legend>%s</fieldset>';

		return sprintf( $fieldset_format, $taxonomy_data->object->labels->name, $inputs_markup );
	}

}
