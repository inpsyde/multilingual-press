<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\Asset\AssetManager;

use function Inpsyde\MultilingualPress\get_post_taxonomies_with_terms;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
class AdvancedPostTranslatorFields {

	const COPIED_POST = 'copied';

	const INPUT_ID_BASE = 'mlp-translation-data';

	const INPUT_NAME_BASE = 'mlp_translation_data';

	const POST_CONTENT = 'content';

	const POST_EXCERPT = 'excerpt';

	const POST_NAME = 'name';

	const POST_TITLE = 'title';

	const REMOTE_POST_ID = 'remote_post_id';

	const SOURCE_POST_ID = 'source_post_id';

	const SYNC_THUMBNAIL = 'thumbnail';

	const TAXONOMY = 'tax';

	/**
	 * @var AssetManager
	 */
	private $asset_manager;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param AssetManager $asset_manager
	 */
	public function __construct( AssetManager $asset_manager ) {

		$this->asset_manager = $asset_manager;
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	public function render_top_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		$this->render_posts_id_input( $source_post, $remote_site_id, $remote_post );
		$this->render_title_input( $source_post, $remote_site_id, $remote_post );
		$this->render_name_input( $remote_site_id, $remote_post );
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	public function render_main_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		$this->render_editor( $source_post, $remote_site_id, $remote_post );
		$this->render_excerpt_input( $source_post, $remote_site_id, $remote_post );
		$this->render_sync_thumbnail_input( $source_post, $remote_site_id );
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	public function render_bottom_fields( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		$this->render_taxonomies_input( $source_post, $remote_site_id, $remote_post );
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	private function render_posts_id_input(
		\WP_Post $source_post,
		int $remote_site_id,
		\WP_Post $remote_post = null
	) {

		?>
		<input type="hidden"
			name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::SOURCE_POST_ID ) ); ?>"
			value="<?php echo esc_attr( $source_post->ID ); ?>">
		<input type="hidden"
			name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::REMOTE_POST_ID ) ); ?>"
			value="<?php echo esc_attr( $remote_post->ID ?? 0 ); ?>">
		<?php
	}

	/**
	 * @param \WP_Post      $source_post
	 * @param int           $remote_site_id
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	private function render_title_input( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		if ( ! post_type_supports( $source_post->post_type, 'title' ) ) {
			return;
		}

		/** This filter is documented in wp-admin/edit-form-advanced.php */
		$placeholder = apply_filters( 'enter_title_here', __( 'Enter title here', 'multilingualpress' ), $source_post );
		?>
		<div class="mlp-titlediv">
			<div>
				<input
					type="text"
					name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::POST_TITLE ) ); ?>"
					value="<?php echo $remote_post ? esc_attr( $remote_post->post_title ) : ''; ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					size="30"
					class="mlp-title"
					id="<?php echo esc_attr( $this->field_id( $remote_site_id, self::POST_TITLE ) ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * @param int      $remote_site_id Remote site id.
	 * @param \WP_Post $remote_post    Remote post.
	 *
	 * @return void
	 */
	private function render_name_input( int $remote_site_id, \WP_Post $remote_post = null ) {

		$id = $this->field_id( $remote_site_id, self::POST_NAME );

		$value = $remote_post ? $remote_post->post_name : '';
		if ( ! $value && $remote_post && $remote_post->post_title ) {
			$value = sanitize_title( $remote_post->post_title );
		}
		$value = urldecode( $value );
		?>
		<div class="mlp-namediv">
			<div>
				<label for="<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Post Name:', 'multilingualpress' ); ?><br>
					<input
						type="text"
						name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::POST_NAME ) ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						placeholder="<?php esc_attr_e( 'Enter name here', 'multilingualpress' ); ?>"
						size="30"
						class="mlp-name"
						id="<?php echo esc_attr( $id ); ?>">
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * @param \WP_Post $source_post
	 * @param int      $remote_site_id
	 * @param \WP_Post $remote_post
	 *
	 * @return void
	 */
	private function render_editor( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		if ( ! post_type_supports( $source_post->post_type, 'editor' ) ) {
			return;
		}

		$this->asset_manager->enqueue_script_with_data( 'multilingualpress-admin', 'mlpCopyPostSettings', [
			'action' => AdvancedPostTranslatorAJAXHandler::AJAX_ACTION,
			'siteID' => get_current_blog_id(),
		] );

		$content = $remote_post ? $remote_post->post_content : '';

		$editor_id = $this->field_id( $remote_site_id, self::POST_CONTENT );

		$this->render_copy_button( $editor_id, $remote_site_id );

		wp_editor( $content, $editor_id, [
			'tabindex'      => false,
			'editor_height' => 150,
			'resize'        => true,
			'textarea_name' => $this->field_name( $remote_site_id, self::POST_CONTENT ),
			'media_buttons' => false,
			'tinymce'       => [
				'resize' => true,
			],
		] );
	}

	/**
	 * @param \WP_Post $source_post
	 * @param int      $remote_site_id
	 * @param \WP_Post $remote_post
	 *
	 * @return void
	 */
	private function render_excerpt_input( \WP_Post $source_post, int $remote_site_id, \WP_Post $remote_post = null ) {

		if ( ! post_type_supports( $source_post->post_type, 'excerpt' ) ) {
			return;
		}

		$id = $this->field_id( $remote_site_id, self::POST_EXCERPT );

		$value = $remote_post ? $remote_post->post_excerpt : '';
		?>
		<div class="mlp-excerptdiv">
			<div>
				<label for="<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Post Excerpt:', 'multilingualpress' ); ?>
				</label>
				<textarea
					name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::POST_EXCERPT ) ); ?>"
					placeholder="<?php esc_attr_e( 'Enter excerpt here', 'multilingualpress' ); ?>"
					class="mlp-excerpt"
					id="<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * @param \WP_Post $source_post
	 * @param int      $remote_site_id
	 *
	 * @return void
	 */
	private function render_sync_thumbnail_input( \WP_Post $source_post, int $remote_site_id ) {

		if ( ! post_type_supports( $source_post->post_type, 'thumbnail' ) ) {
			return;
		}

		$id = $this->field_id( $remote_site_id, self::SYNC_THUMBNAIL );
		?>
		<p>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::SYNC_THUMBNAIL ) ); ?>"
					value="1"
					id="<?php echo esc_attr( $id ); ?>">
				<?php esc_html_e( 'Copy the featured image of the source post.', 'multilingualpress' ); ?>
				<span class="description">
					<?php
					esc_html_e( 'Overwrites an existing featured image in the target post.', 'multilingualpress' );
					?>
				</span>
			</label>
		</p>
		<?php
	}

	/**
	 * @param \WP_Post $source_post
	 * @param int      $remote_site_id
	 * @param \WP_Post $remote_post
	 *
	 * @return void
	 */
	private function render_taxonomies_input(
		\WP_Post $source_post,
		int $remote_site_id,
		\WP_Post $remote_post = null
	) {

		$terms_post = $remote_post;
		if ( ! $terms_post ) {
			$terms_post = new \WP_Post( (object) [
				'ID'        => 0,
				'post_type' => $source_post->post_type,
			] );
		}

		switch_to_blog( $remote_site_id );
		$taxonomies = get_post_taxonomies_with_terms( $terms_post );
		restore_current_blog();

		if ( ! $taxonomies ) {
			return;
		}

		/**
		 * Filter mutually exclusive taxonomies.
		 *
		 * @param string[] $taxonomies Mutually exclusive taxonomy names.
		 */
		$exclusive_tax = (array) apply_filters( 'mlp_mutually_exclusive_taxonomies', [ 'post_format' ] );

		$toggle_id = "tax_toggle_{$remote_site_id}";
		?>
		<button
			type="button"
			name="toggle_<?php echo esc_attr( $remote_site_id ); ?>"
			data-toggle-target="#<?php echo esc_attr( $toggle_id ); ?>"
			class="button secondary mlp-click-toggler">
			<?php esc_html_e( 'Change taxonomies', 'multilingualpress' ); ?>
		</button>
		<div class="hidden" id="<?php echo esc_attr( $toggle_id ); ?>">
			<div class="mlp-taxonomy-fieldset-container">
				<?php
				foreach ( $taxonomies as $taxonomy => $taxonomy_data ) {
					$this->render_terms_input(
						$taxonomy_data,
						$remote_site_id,
						in_array( $taxonomy, $exclusive_tax, true ) ? 'radio' : 'checkbox',
						$remote_post
					);
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * @param string $editor_id      tinyMCE editor ID.
	 * @param int    $remote_site_id Remote site ID.
	 *
	 * @return void
	 */
	private function render_copy_button( string $editor_id, int $remote_site_id ) {

		$matches = [];

		preg_match( '~mlp-translation-data-(\d+)-content~', $editor_id, $matches );
		if ( empty( $matches[1] ) ) {
			return;
		}
		?>
		<div class="wp-media-buttons">
			<button class="button mlp-copy-post-button" data-site-id="<?php echo esc_attr( $matches[1] ); ?>">
				<span class="wp-media-buttons-icon"></span>
				<?php esc_html_e( 'Copy source post', 'multilingualpress' ); ?>
			</button>
		</div>
		<input
			type="hidden"
			name="<?php echo esc_attr( $this->field_name( $remote_site_id, self::COPIED_POST ) ); ?>"
			value=""
			id="<?php echo esc_attr( $this->field_id( $remote_site_id, self::COPIED_POST ) ); ?>">
		<?php
	}

	/**
	 * @param \stdClass     $taxonomy_data
	 * @param int           $remote_site_id
	 * @param string        $input_type Either 'checkbox' (e.g., for categories) or 'radio' (e.g., for post formats).
	 *
	 * @param \WP_Post|null $remote_post
	 *
	 * @return void
	 */
	private function render_terms_input(
		\stdClass $taxonomy_data,
		int $remote_site_id,
		string $input_type,
		\WP_Post $remote_post = null
	) {

		static $tags;

		if ( empty( $taxonomy_data->terms ) ) {
			return;
		}

		$name = $this->field_name( $remote_site_id, self::TAXONOMY ) . "[{$taxonomy_data->object->name}]";

		$inputs_markup = '';

		$inputs_format = '<label for="%2$s">';
		$inputs_format .= '<input type="%3$s" name="%4$s[]" id="%2$s" value="%5$s"%6$s> %1$s';
		$inputs_format .= '</label><br>';

		/** @var \stdClass $term_data */
		foreach ( $taxonomy_data->terms as $term_data ) {

			/** @var \WP_Term $term */
			$term = $term_data->object;

			$assigned = $remote_post ? checked( $term_data->assigned, true, false ) : '';

			$inputs_markup .= sprintf(
				$inputs_format,
				esc_html( $term->name ),
				esc_attr( "term-{$remote_site_id}-{$term->term_taxonomy_id}" ),
				esc_attr( $input_type ),
				esc_attr( $name ),
				esc_attr( $term->term_id ),
				$assigned
			);
		}

		$html = sprintf(
			'<fieldset class="mlp-taxonomy-box"><legend>%s</legend>%s</fieldset>',
			esc_html( $taxonomy_data->object->labels->name ),
			$inputs_markup
		);

		if ( ! $tags ) {
			$tags = [
				'br'       => [],
				'fieldset' => [
					'class' => true,
				],
				'input'    => [
					'checked' => true,
					'id'      => true,
					'name'    => true,
					'type'    => true,
					'value'   => true,
				],
				'label'    => [
					'for' => true,
				],
				'legend'   => [],
			];
		}

		echo wp_kses( $html, $tags );
	}

	/**
	 * @param int    $site_id
	 * @param string $name
	 *
	 * @return string
	 */
	private function field_name( int $site_id, string $name ): string {

		return self::INPUT_NAME_BASE . "[{$site_id}][{$name}]";
	}

	/**
	 * @param int    $site_id
	 * @param string $name
	 *
	 * @return string
	 */
	private function field_id( int $site_id, string $name ): string {

		return self::INPUT_ID_BASE . "-{$site_id}-{$name}";
	}
}
