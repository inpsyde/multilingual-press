<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Metabox\MetaboxUpdater;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
final class PostTranslationMetaboxUpdater implements MetaboxUpdater {

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
	private $post;

	/**
	 * Constructor.
	 *
	 * @param string   $language
	 * @param int      $site_id
	 * @param \WP_Post $post
	 */
	public function __construct( int $site_id, string $language, \WP_Post $post = null ) {

		$this->site_id = $site_id;
		$this->language = $language;
		$this->post = $post;
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

	public function update( ServerRequest $request ): bool {
		// TODO: Implement update() method.
	}
}