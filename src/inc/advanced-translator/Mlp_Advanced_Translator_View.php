<?php

/**
 * Class Mlp_Advanced_Translator_View
 *
 * Data model for post translation. Handles inserts of new posts only.
 *
 * @version 2015.06.29
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Advanced_Translator_View {

	/**
	 * @var Mlp_Advanced_Translator_Data_Interface
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @param Mlp_Advanced_Translator_Data_Interface $data
	 */
	public function __construct( Mlp_Advanced_Translator_Data_Interface $data ) {

		$this->data = $data;
	}

	/**
	 * Add a button next to the media button to copy the source post.
	 *
	 * @wp-hook media_buttons
	 *
	 * @param string $editor_id      tinyMCE editor ID.
	 * @param int    $remote_site_id Remote site ID.
	 *
	 * @return  void
	 */
	public function show_copy_button( $editor_id, $remote_site_id ) {

		$matches = array();

		preg_match( '~mlp-translation-data-(\d+)-content~', $editor_id, $matches );
		if ( empty( $matches[1] ) ) {
			return;
		}

		$name = $this->get_name( $remote_site_id, 'copied_post' );

		$id = $this->get_id( $remote_site_id, 'copied-post' );
		?>
		<a href="#" class="button mlp-copy-post-button dashicons-before dashicons-image-rotate-right"
			data-site-id="<?php echo esc_attr( $matches[1] ); ?>">
			<?php esc_html_e( 'Copy source post', 'multilingual-press' ); ?>
		</a>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="" id="<?php echo esc_attr( $id ); ?>">
		<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post Remote post
	 *
	 * @return void
	 */
	public function show_title(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post
	) {

		$name = $this->get_name( $remote_blog_id, 'title' );

		$placeholder = $this->get_placeholder_title( $post );

		$id = $this->get_id( $remote_blog_id, 'title' );
		?>
		<div class="mlp-titlediv">
			<div>
				<input type="text" name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $post->post_title ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>" size="30" class="mlp-title"
					id="<?php echo esc_attr( $id ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post Remote post
	 *
	 * @return void
	 */
	public function show_name(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post
	) {

		$id = $this->get_id( $remote_blog_id, 'name' );

		$name = $this->get_name( $remote_blog_id, 'name' );

		$value = $post->post_name;
		if ( empty( $value ) ) {
			$value = sanitize_title( $post->post_title );
		}
		$value = urldecode( $value );
		?>
		<div class="mlp-namediv">
			<div>
				<label for="<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Post Name:', 'multilingual-press' ); ?><br>
					<input type="text" name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						placeholder="<?php echo esc_attr__( 'Enter name here', 'multilingual-press' ); ?>" size="30"
						class="mlp-name" id="<?php echo esc_attr( $id ); ?>">
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post Remote post
	 *
	 * @return void
	 */
	public function show_excerpt(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post
	) {

		$id = $this->get_id( $remote_blog_id, 'excerpt' );

		$name = $this->get_name( $remote_blog_id, 'excerpt' );

		$value = $post->post_excerpt;
		?>
		<div class="mlp-excerptdiv">
			<div>
				<label for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Post Excerpt:', 'multilingual-press' ); ?></label>
				<textarea name="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php echo esc_attr__( 'Enter excerpt here', 'multilingual-press' ); ?>"
					class="mlp-excerpt"
					id="<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $remote_post
	 *
	 * @return void
	 */
	public function show_editor(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $remote_post
	) {

		$editor_id = $this->get_id( $remote_blog_id, 'content' );

		$this->show_copy_button( $editor_id, $remote_blog_id );

		wp_editor( $remote_post->post_content, $editor_id, array(
			'tabindex'      => false,
			'editor_height' => 150,
			'resize'        => true,
			'textarea_name' => $this->get_name( $remote_blog_id, 'content' ),
			'media_buttons' => false,
			'tinymce'       => array(
				'resize' => true,
			),
		) );
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function show_thumbnail_checkbox(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post
	) {

		$id = $this->get_id( $remote_blog_id, 'thumbnail' ) . '_id';

		$name = $this->get_name( $remote_blog_id, 'thumbnail' );
		?>
		<p>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"
					id="<?php echo esc_attr( $id ); ?>">
				<?php esc_html_e( 'Copy the featured image of the source post.', 'multilingual-press' ); ?>
				<span class="description">
					<?php esc_html_e( 'Overwrites an existing featured image in the target post.', 'multilingual-press' ); ?>
				</span>
			</label>
		</p>
		<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function show_taxonomies(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post
	) {

		$taxonomies = $this->data->get_taxonomies( $post, $remote_blog_id );
		if ( empty( $taxonomies['inclusive'] ) && empty( $taxonomies['exclusive'] ) ) {
			return;
		}

		$toggle_id = 'tax_toggle_' . $remote_blog_id;
		?>
		<button type="button" name="toggle_<?php echo esc_attr( $remote_blog_id ); ?>"
			data-toggle-target="#<?php echo esc_attr( $toggle_id ); ?>"
			class="button secondary mlp-click-toggler">
			<?php echo esc_html__( 'Change taxonomies', 'multilingual-press' ); ?>
		</button>
		<div class="hidden" id="<?php echo esc_attr( $toggle_id ); ?>">
			<?php if ( ! empty( $taxonomies['inclusive'] ) ) : ?>
				<div class="mlp-taxonomy-fieldset-container">
					<?php foreach ( $taxonomies['inclusive'] as $taxonomy => $data ) : ?>
						<?php $this->list_terms( $taxonomy, $data, $remote_blog_id, 'checkbox' ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $taxonomies['exclusive'] ) ) : ?>
				<div class="mlp-taxonomy-fieldset-container">
					<?php foreach ( $taxonomies['exclusive'] as $taxonomy => $data ) : ?>
						<?php $this->list_terms( $taxonomy, $data, $remote_blog_id, 'radio' ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function blog_id_input( WP_Post $source_post, $remote_blog_id, WP_Post $post ) {

		$source_name = $this->get_name( $remote_blog_id, 'source_post_id' );

		$remote_name = $this->get_name( $remote_blog_id, 'remote_post_id' );
		?>
		<input type="hidden" name="<?php echo esc_attr( $source_name ); ?>"
			value="<?php echo esc_attr( $source_post->ID ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $remote_name ); ?>" value="<?php echo absint( $post->ID ); ?>">
		<?php
	}

	/**
	 * Get the value for the name attribute.
	 *
	 * @param int    $blog_id
	 * @param string $name
	 *
	 * @return string
	 */
	private function get_name( $blog_id, $name ) {

		return $this->data->get_name_base() . '[' . (int) $blog_id . '][' . $name . ']';
	}

	/**
	 * Get the value for the id attribute.
	 *
	 * @param int    $blog_id
	 * @param string $name
	 *
	 * @return string
	 */
	private function get_id( $blog_id, $name ) {

		return $this->data->get_id_base() . '-' . (int) $blog_id . '-' . $name;
	}

	/**
	 * Get placeholder attribute text.
	 *
	 * @param WP_Post $post
	 *
	 * @return string|void
	 */
	private function get_placeholder_title( WP_Post $post ) {

		$placeholder = __( 'Enter title here', 'multilingual-press' );

		/** This filter is documented in wp-admin/edit-form-advanced.php */
		return apply_filters( 'enter_title_here', $placeholder, $post );
	}

	/**
	 * List terms which are mutually exclusive, like post formats.
	 *
	 * @param string $taxonomy
	 * @param array  $data
	 * @param int    $remote_blog_id
	 * @param string $input_type     Either 'checkbox' (e.g., for categories) or 'radio' (e.g., for post formats).
	 *
	 * @return void
	 */
	private function list_terms( $taxonomy, array $data, $remote_blog_id, $input_type ) {

		$name = $this->get_name( $remote_blog_id, 'tax' ) . '[' . $taxonomy . ']';

		$html = '';

		foreach ( $data['terms'] as $term ) {
			$html .= sprintf(
				'<label for="%2$s"><input type="%3$s" name="%4$s[]" id="%2$s" value="%5$s"%6$s>%1$s</label><br>',
				esc_html( $term->name ),
				esc_attr( $term->slug ) . '_id',
				esc_attr( $input_type ),
				esc_attr( $name ),
				esc_attr( $term->term_id ),
				checked( $term->active, true, false )
			);
		}

		$this->term_box( $data['properties']->labels->name, $html );
	}

	/**
	 * Container HTML for term selection.
	 *
	 * @param string $title Taxonomy name
	 * @param string $html
	 *
	 * @return void
	 */
	private function term_box( $title, $html ) {

		$tags = array(
			'br'    => array(),
			'input' => array(
				'checked' => true,
				'id'      => true,
				'name'    => true,
				'type'    => true,
				'value'   => true,
			),
			'label' => array(
				'for' => true,
			),
		);
		?>
		<fieldset class="mlp-taxonomy-box">
			<legend><?php echo esc_html( $title ); ?></legend>
			<?php echo wp_kses( $html, $tags ); ?>
		</fieldset>
	<?php
	}

	/**
	 * Shows a warning message in the metabox
	 * that the remote post is trashed.
	 *
	 * @return void
	 */
	public function show_trashed_message() {

		?>
		<div class="mlp-warning">
			<p>
			<?php
			esc_html_e(
				'The remote post is trashed. You are not able to edit it here. If you want to, restore the remote post. Also mind the options below.',
				'multilingual-press'
			);
			?>
			</p>
		</div>
		<?php
	}
}
