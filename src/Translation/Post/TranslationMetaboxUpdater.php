<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxUpdater;
use Inpsyde\MultilingualPress\Translation\Metabox\PostMetaboxUpdater;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class TranslationMetaboxUpdater implements PostMetaboxUpdater {

	/**
	 * @var array
	 */
	private $data = [];
	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var string
	 */
	private $language;

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
	 * @param string   $language
	 * @param int      $site_id
	 * @param \WP_Post $remote_post
	 */
	public function __construct( int $site_id, string $language, \WP_Post $remote_post = null ) {

		$this->site_id     = $site_id;
		$this->language    = $language;
		$this->remote_post = $remote_post;
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return PostMetaboxUpdater
	 */
	public function with_post( \WP_Post $post ): PostMetaboxUpdater {

		$this->post = $post;

		return $this;
	}

	/**
	 * @param array $data
	 *
	 * @return MetaboxUpdater
	 */
	public function with_data( array $data ): MetaboxUpdater {

		$this->data = array_merge( $this->data, $data );

		return $this;
	}

	/**
	 * @param ServerRequest $request
	 *
	 * @return bool
	 */
	public function update( ServerRequest $request ): bool {
		// TODO: Implement update() method.
	}
}