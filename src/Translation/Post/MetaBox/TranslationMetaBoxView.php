<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxView;
use Inpsyde\MultilingualPress\Common\Admin\MetaBox\Post\PostMetaBoxView;

use function Inpsyde\MultilingualPress\get_site_language;

/**
 * Meta box view implementation for post translation.
 *
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class TranslationMetaBoxView implements PostMetaBoxView {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_RENDER_PREFIX = 'multilingualpress.post_translation_meta_box_';

	/**
	 * Position name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const POSITION_BOTTOM = 'bottom';

	/**
	 * Position name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const POSITION_MAIN = 'main';

	/**
	 * Position name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const POSITION_TOP = 'top';

	/**
	 * Position names.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
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
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var \WP_Post
	 */
	private $remote_post;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param int      $site_id     Site ID.
	 * @param \WP_Post $remote_post Optional. Remote post object. Defaults to null.
	 */
	public function __construct( int $site_id, \WP_Post $remote_post = null ) {

		$this->site_id = $site_id;

		$this->remote_post = $remote_post;
	}

	/**
	 * Returns an instance with the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to be set.
	 *
	 * @return MetaBoxView
	 */
	public function with_data( array $data ): MetaBoxView {

		$clone = clone $this;

		$clone->data = array_merge( $this->data, $data );

		return $clone;
	}

	/**
	 * Returns an instance with the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object to set.
	 *
	 * @return PostMetaBoxView
	 */
	public function with_post( \WP_Post $post ): PostMetaBoxView {

		$clone = clone $this;

		$clone->post = $post;

		return $clone;
	}

	/**
	 * Returns the rendered HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string Rendered HTML.
	 */
	public function render(): string {

		if ( ! $this->post ) {
			return '';
		}

		$args = [
			$this->post,
			$this->site_id,
			get_site_language( $this->site_id ),
			$this->remote_post,
		];

		ob_start();
		?>
		<div class="holder mlp-translation-meta-box">
			<?php
			/**
			 * Fires right before the main content of the meta box.
			 *
			 * @since 3.0.0
			 *
			 * @param \WP_Post      $post                 Source post object.
			 * @param int           $remote_site_id       Remote site ID.
			 * @param string        $remote_site_language Remote site language.
			 * @param \WP_Post|null $remote_post          Remote post object.
			 */
			do_action( self::ACTION_RENDER_PREFIX . self::POSITION_TOP, ...$args );

			/**
			 * Fires along with the main content of the meta box.
			 *
			 * @since 3.0.0
			 *
			 * @param \WP_Post      $post                 Source post object.
			 * @param int           $remote_site_id       Remote site ID.
			 * @param string        $remote_site_language Remote site language.
			 * @param \WP_Post|null $remote_post          Remote post object.
			 */
			do_action( self::ACTION_RENDER_PREFIX . self::POSITION_MAIN, ...$args );

			/**
			 * Fires right after the main content of the meta box.
			 *
			 * @since 3.0.0
			 *
			 * @param \WP_Post      $post                 Source post object.
			 * @param int           $remote_site_id       Remote site ID.
			 * @param string        $remote_site_language Remote site language.
			 * @param \WP_Post|null $remote_post          Remote post object.
			 */
			do_action( self::ACTION_RENDER_PREFIX . self::POSITION_BOTTOM, ...$args );
			?>
		</div>
		<?php

		return ob_get_clean();
	}
}
