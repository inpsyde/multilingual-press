<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxView;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class TranslationMetaboxView implements PostMetaboxView {

	const ACTION_RENDER_PREFIX = 'multilingualpress.translation_meta_box_';

	const POSITION_TOP = 'top';

	const POSITION_MAIN = 'main';

	const POSITION_BOTTOM = 'bottom';

	const POSITIONS = [
		self::POSITION_TOP,
		self::POSITION_MAIN,
		self::POSITION_BOTTOM,
	];

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
	private $remote_post;

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * Constructor.
	 *
	 * @param int      $site_id
	 * @param string   $language
	 * @param \WP_Post $remote_post
	 */
	public function __construct( int $site_id, string $language, \WP_Post $remote_post = null ) {

		$this->language    = $language;
		$this->site_id     = $site_id;
		$this->remote_post = $remote_post;
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
	 * @param \WP_Post $post
	 *
	 * @return PostMetaboxView
	 */
	public function with_post( \WP_Post $post ): PostMetaboxView {

		$this->post = $post;

		return $this;
	}

	/**
	 * @return string
	 */
	public function render(): string {

		if ( ! $this->post ) {
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
				self::ACTION_RENDER_PREFIX . self::POSITION_TOP,
				$this->post,
				$this->site_id,
				$this->language,
				$this->remote_post
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
				self::ACTION_RENDER_PREFIX . self::POSITION_MAIN,
				$this->post,
				$this->site_id,
				$this->language,
				$this->remote_post
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
				self::ACTION_RENDER_PREFIX . self::POSITION_BOTTOM,
				$this->post,
				$this->site_id,
				$this->language,
				$this->remote_post
			);
			?>
		</div>
		<!-- /MultilingualPress Translation Box -->
		<?php

		return ob_get_clean();

	}

	/**
	 * @param string   $position
	 * @param callable $callback
	 * @param int      $priority
	 */
	public function inject_in_view( string $position, callable $callback, int $priority = 10 ) {

		if ( ! in_array( $position, self::POSITIONS, true ) ) {
			return;
		}

		add_action(
			self::ACTION_RENDER_PREFIX . $position,
			function ( ...$args ) use ( $callback ) {

				ob_start();
				$return = $callback( ...$args );
				$echo   = ob_get_clean();

				if ( trim( $echo ) ) {
					echo $echo;
				} elseif ( is_string( $return ) && $return ) {
					echo $return;
				}
			},
			$priority,
			4
		);

	}
}