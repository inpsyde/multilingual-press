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
		<div class="holder mlp_advanced_translator_metabox clear">
		<?php

		$data = (object) $meta_box['args'];

		/**
		 * Perform additional actions on top of the translation box.
		 *
		 * @param WP_Post $post
		 * @param int     $data->remote_blog_id
		 * @param WP_Post $data->remote_post
		 */
		do_action(
			'mlp_translation_meta_box_top',
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Perform additional actions on top of the translation box.
		 *
		 * @param WP_Post $post
		 * @param int     $data->remote_blog_id
		 * @param WP_Post $data->remote_post
		 */
		do_action(
			'mlp_translation_meta_box_top_' . $data->remote_blog_id,
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Meta box main content.
		 *
		 * @param WP_Post $post
		 * @param int     $data->remote_blog_id
		 * @param WP_Post $data->remote_post
		 */
		do_action(
			'mlp_translation_meta_box_main',
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Meta box main content.
		 *
		 * @param WP_Post $post
		 * @param int     $data->remote_blog_id
		 * @param WP_Post $data->remote_post
		 */
		do_action(
			'mlp_translation_meta_box_main_' . $data->remote_blog_id,
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Perform additional actions below the translation box.
		 *
		 * @param WP_Post $post
		 * @param int     $data->remote_blog_id
		 * @param WP_Post $data->remote_post
		 */
		do_action(
			'mlp_translation_meta_box_bottom',
			$post,
			$data->remote_blog_id,
			$data->remote_post
		);

		/**
		 * Perform additional actions below the translation box.
		 *
		 * @param WP_Post $post
		 * @param int     $data->remote_blog_id
		 * @param WP_Post $data->remote_post
		 */
		do_action(
			'mlp_translation_meta_box_bottom_' . $data->remote_blog_id,
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
	 * @return void
	 */
	public function show_title( /** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $post ) {

		if ( ! empty ( $post->post_title ) )
			print "<h2 class='headline' style='margin:0;'>$post->post_title</h2>";
	}

	/**
	 * @param WP_Post $source_post
	 * @param int     $remote_blog_id
	 * @param WP_Post $remote_post
	 * @return void
	 */
	public function show_editor( /** @noinspection PhpUnusedParameterInspection */
		WP_Post $source_post, $remote_blog_id, WP_Post $remote_post ) {

		$lines = substr_count( $remote_post->post_content, "\n" ) + 1;
		$rows  = min( $lines, 10 );

		printf(
			'<textarea class="large-text cols="80" rows="%d$1" placeholder="%2$s" readonly>%3$s</textarea>',
			$rows,
			esc_attr_x( 'No content yet.', 'placeholder for empty translation textarea', 'multilingualpress' ),
			esc_textarea( $remote_post->post_content )
		);
	}

	/**
	 * @param  WP_Post $post
	 * @param  int     $blog_id
	 * @return void
	 */
	public function show_translation_checkbox( /** @noinspection PhpUnusedParameterInspection */
		WP_Post $post, $blog_id ) {

		$id = (int) $blog_id;
		?>
		<p>
			<label for="translate_this_post_<?php print $id; ?>">
				<input
					type  = "checkbox"
					id    = "translate_this_post_<?php print $id; ?>"
					name  = "mlp_to_translate[]"
					value = "<?php print $id; ?>" />
				<?php _e( 'Translate this post', 'multilingualpress' ); ?>
			</label>
		</p>
	<?php
	}

	/**
	 * Explain what the pro version can do here.
	 *
	 * @return void
	 */
	public function show_upgrade_notice() {

		static $called = FALSE;

		if ( $called )
			return;

		$called = TRUE;

		$text = _x(
			'In <a href="%s">MultilingualPress Pro</a>, you can edit the translation right here, copy the featured image, set tags and categories, and you can change the translation relationship.',
			'%s = link to MultilingualPress Pro',
			'multilingualpress'
		);

		$url = __(
			'http://marketpress.com/product/multilingual-press-pro/',
			'multilingualpress'
		);
		$url = esc_url( $url );

		// A broken translation might mess up the URL.
		if ( '' === $url )
			$url = 'http://marketpress.com/product/multilingual-press-pro/';

		printf( "<p>$text</p>", $url );
	}
}