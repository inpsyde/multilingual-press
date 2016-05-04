<?php
/**
 * Meta box output.
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2014.03.13
 * @license GPL
 */
class Mlp_Translation_Metabox_View {

	/**
	 * @var Inpsyde_Nonce_Validator_Interface
	 */
	private $nonce;

	/**
	 * Constructor.
	 *
	 * @param Inpsyde_Nonce_Validator_Interface $nonce
	 */
	public function __construct( Inpsyde_Nonce_Validator_Interface $nonce ) {
		$this->nonce   = $nonce;
	}

	/**
	 * Basic meta box frame.
	 *
	 * @param WP_Post $post
	 * @param array   $meta_box
	 * @return void
	 */
	public function render( WP_Post $post, Array $meta_box ) {

		wp_nonce_field( $this->nonce->get_action(), $this->nonce->get_name() );
		?>
		<!-- MultilingualPress Translation Box -->
		<div class="holder mlp-translation-meta-box">
		<?php

		$data = (object) $meta_box['args'];

		/**
		 * Runs before the main content of the meta box.
		 *
		 * @param WP_Post $post           Post object.
		 * @param int     $remote_blog_id Remote blog ID.
		 * @param WP_Post $remote_post    Remote post object.
		 */
		do_action(
			'mlp_translation_meta_box_top',
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Runs before the main content of the meta box.
		 *
		 * @param WP_Post $post           Post object.
		 * @param int     $remote_blog_id Remote blog ID.
		 * @param WP_Post $remote_post    Remote post object.
		 */
		do_action(
			"mlp_translation_meta_box_top_{$data->remote_blog_id}",
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Runs with the main content of the meta box.
		 *
		 * @param WP_Post $post           Post object.
		 * @param int     $remote_blog_id Remote blog ID.
		 * @param WP_Post $remote_post    Remote post object.
		 */
		do_action(
			'mlp_translation_meta_box_main',
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Runs with the main content of the meta box.
		 *
		 * @param WP_Post $post           Post object.
		 * @param int     $remote_blog_id Remote blog ID.
		 * @param WP_Post $remote_post    Remote post object.
		 */
		do_action(
			"mlp_translation_meta_box_main_{$data->remote_blog_id}",
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Runs before the main content of the meta box.
		 *
		 * @param WP_Post $post           Post object.
		 * @param int     $remote_blog_id Remote blog ID.
		 * @param WP_Post $remote_post    Remote post object.
		 */
		do_action(
			'mlp_translation_meta_box_bottom',
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Runs before the main content of the meta box.
		 *
		 * @param WP_Post $post           Post object.
		 * @param int     $remote_blog_id Remote blog ID.
		 * @param WP_Post $remote_post    Remote post object.
		 */
		do_action(
			"mlp_translation_meta_box_bottom_{$data->remote_blog_id}",
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);
		?>
		</div>
		<!-- /MultilingualPress Translation Box -->
	<?php
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function show_title(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post
	) {

		if ( ! empty( $post->post_title ) ) {
			echo '<h2 class="headline" style="margin: 0;">' . esc_attr( $post->post_title ) . '</h2>';
		}
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

		$lines = substr_count( $remote_post->post_content, "\n" ) + 1;
		$rows  = min( $lines, 10 );

		printf(
			'<textarea class="large-text" cols="80" rows="%d$1" placeholder="%2$s" readonly>%3$s</textarea>',
			esc_attr( $rows ),
			esc_attr_x( 'No content yet.', 'placeholder for empty translation textarea', 'multilingual-press' ),
			esc_textarea( $remote_post->post_content )
		);
	}

	/**
	 * @param  WP_Post $post
	 * @param  int     $blog_id
	 * @return void
	 */
	public function show_translation_checkbox(
		/** @noinspection PhpUnusedParameterInspection */
		WP_Post $post, $blog_id
	) {

		$id = (int) $blog_id;
		?>
		<p>
			<label for="translate_this_post_<?php echo esc_attr( $id ); ?>">
				<input
					type="checkbox"
					id="translate_this_post_<?php echo esc_attr( $id ); ?>"
					name="mlp_to_translate[]"
					value="<?php echo esc_attr( $id ); ?>" />
				<?php esc_html_e( 'Translate this post', 'multilingual-press' ); ?>
			</label>
		</p>
		<?php
	}

}
