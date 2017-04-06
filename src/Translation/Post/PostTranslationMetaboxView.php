<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxView;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class PostTranslationMetaboxView implements MetaboxView {

	/**
	 * @var array
	 */
	private $data = [];
	/**
	 * @var string
	 */
	private $language;
	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * Constructor.
	 *
	 * @param int      $site_id
	 * @param string   $language
	 * @param \WP_Post $post
	 */
	public function __construct( int $site_id, string $language, \WP_Post $post = null ) {

		$this->language = $language;
		$this->site_id  = $site_id;
		$this->post     = $post;
	}

	/**
	 * @param array $data
	 *
	 * @return MetaboxView
	 */
	public function with_data( array $data ): MetaboxView {

		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * @return string
	 */
	public function render(): string {

		$post = $this->data['post'] ?? null;
		if ( ! $post ) {
			return '';
		}

		ob_start();
		?>
		<!-- MultilingualPress Translation Box -->
		<div class="holder mlp-translation-meta-box">
			<?php

			/**
			 * Runs before the main content of the meta box.
			 *
			 * @param \WP_Post      $post           Source post object.
			 * @param int           $remote_blog_id Remote site ID.
			 * @param string        $language       Remote site language.
			 * @param \WP_Post|null $remote_post    Remote post object.
			 */
			do_action(
				'multilingualpress.translation_meta_box_top',
				$post,
				$this->site_id,
				$this->language,
				$this->post
			);

			/**
			 * Runs with the main content of the meta box.
			 *
			 * @param \WP_Post      $post           Source post object.
			 * @param int           $remote_blog_id Remote site ID.
			 * @param string        $language       Remote site language.
			 * @param \WP_Post|null $remote_post    Remote post object.
			 */
			do_action(
				'multilingualpress.translation_meta_box_main',
				$post,
				$this->site_id,
				$this->language,
				$this->post
			);

			/**
			 * Runs before the main content of the meta box.
			 *
			 * @param \WP_Post      $post           Source post object.
			 * @param int           $remote_blog_id Remote site ID.
			 * @param string        $language       Remote site language.
			 * @param \WP_Post|null $remote_post    Remote post object.
			 */
			do_action(
				'multilingualpress.translation_meta_box_bottom',
				$post,
				$this->site_id,
				$this->language,
				$this->post
			);
			?>
		</div>
		<!-- /MultilingualPress Translation Box -->
		<?php

		return ob_get_clean();

	}
}