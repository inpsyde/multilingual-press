<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\API\SiteRelations;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\AllowedPostTypes;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
final class SourcePostSaveContext implements \ArrayAccess {

	const SITE_ID = 'source_site_id';

	const POST_TYPE = 'real_post_type';

	const POST_ID = 'real_post_id';

	const POST_STATUS = 'original_post_status';

	const FEATURED_IMG_PATH = 'featured_image_path';

	const RELATED_BLOGS = 'related_blogs';

	/**
	 * @var \SplObjectStorage
	 */
	private static $contexts;

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var AllowedPostTypes
	 */
	private $post_types;

	/**
	 * @var SiteRelations
	 */
	private $site_relations;

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param \WP_Post         $post
	 * @param AllowedPostTypes $post_types
	 * @param SiteRelations    $site_relations
	 * @param ServerRequest    $request
	 */
	public function __construct(
		\WP_Post $post,
		AllowedPostTypes $post_types,
		SiteRelations $site_relations,
		ServerRequest $request
	) {

		$this->post = $post;

		$this->post_types = $post_types;

		$this->site_relations = $site_relations;

		$this->request = $request;

		if ( ! self::$contexts ) {
			self::$contexts = new \SplObjectStorage();
		}
	}

	/**
	 * @return array
	 */
	public function to_array(): array {

		$empty_context = [
			self::SITE_ID           => 0,
			self::POST_TYPE         => '',
			self::POST_ID           => 0,
			self::POST_STATUS       => '',
			self::FEATURED_IMG_PATH => '',
			self::RELATED_BLOGS     => [],
		];

		if ( self::$contexts->contains( $this->post ) ) {

			/** @var array $context */
			$context = self::$contexts->offsetGet( $this->post );

			$context = $this->is_valid_save_request( $context ) ? $context : $empty_context;

			return $context;
		}

		$original_post_status = (string) $this->request->body_value( 'original_post_status', INPUT_POST );

		$context = compact( 'original_post_status' );

		if ( ! $this->is_valid_save_request( $context ) ) {
			self::$contexts->attach( $this->post, $context );

			return $empty_context;
		}

		$source_site_id = (int) get_current_blog_id();

		$related_blogs = $this->site_relations->get_related_site_ids( $source_site_id );
		if ( empty( $related_blogs ) ) {
			self::$contexts->attach( $this->post, $context );

			return $empty_context;
		}

		// Get type of post in case of revision
		$real_post_type = $this->real_post_type( $this->post );

		if ( empty( $this->post_types[ $real_post_type ] ) ) {
			self::$contexts->attach( $this->post, $context );

			return $empty_context;
		}

		$request_post_id = (int) $this->request->body_value( 'post_ID', INPUT_REQUEST, FILTER_SANITIZE_NUMBER_INT );

		$context = [
			self::SITE_ID           => $source_site_id,
			self::POST_TYPE         => $real_post_type,
			self::POST_ID           => $request_post_id ?: (int) $this->post->ID,
			self::POST_STATUS       => $original_post_status,
			self::FEATURED_IMG_PATH => $this->featured_image_path( $this->post ),
			self::RELATED_BLOGS     => $related_blogs,
		];

		self::$contexts->attach( self::$contexts, $context );

		return $context;
	}

	/**
	 * Check if the current request should be processed by save().
	 *
	 * @param array $context
	 *
	 * @return bool
	 */
	private function is_valid_save_request( array $context ) {

		static $called = 0;

		if ( ms_is_switched() ) {
			return false;
		}

		// For auto-drafts, 'save_post' is called twice, resulting in doubled drafts for translations.
		$called ++;

		$original_post_status = $context[ self::POST_STATUS] ?? '';

		if ( 'auto-draft' === $original_post_status && 1 < $called ) {
			return false;
		}

		// If context is filled, we only want to check that is not called twice for auto-draft
		if ( array_key_exists( self::SITE_ID, $context) ) {
			return true;
		}

		$post_type = $this->real_post_type( $this->post );

		if ( empty( $this->post_types[ $post_type ] ) ) {
			return false;
		}

		return $this->is_connectable_status( $this->post, $original_post_status );
	}

	/**
	 * Get the real current post type.
	 *
	 * Includes workaround for auto-drafts.
	 *
	 * @param  \WP_Post $post
	 *
	 * @return string
	 */
	private function real_post_type( \WP_Post $post ) {

		$post_id = (int) $post->ID;

		static $post_type = [];
		if ( isset( $post_type[ $post_id ] ) ) {
			return $post_type[ $post_id ];
		}

		$request_post_type = (string) $this->request->body_value( 'post_type', INPUT_POST, FILTER_SANITIZE_STRING );

		$post_type[ $post_id ] = $post->post_type;

		if ( 'revision' === $post->post_type && $request_post_type && 'revision' !== $request_post_type ) {
			$post_type[ $post_id ] = $request_post_type;
		}

		return $post_type[ $post_id ];
	}

	/**
	 * Check post status.
	 *
	 * Includes special hacks for auto-drafts.
	 *
	 * @param \WP_Post $post                 Post object.
	 * @param string   $original_post_status Post status sent with request.
	 *
	 * @return bool
	 */
	private function is_connectable_status( \WP_Post $post, string $original_post_status ) {

		static $connectable_statuses;
		// TODO: Discuss post status "future"...
		$connectable_statuses or $connectable_statuses = [
			'publish',
			'draft',
			'private',
			'auto-draft',
		];

		return
			in_array( $post->post_status, $connectable_statuses, true )
			|| $this->is_auto_draft( $post, $original_post_status );
	}

	/**
	 * Check for hidden auto-draft
	 *
	 * Auto-drafts are sent as revision with a status 'inherit'.
	 * We inspect value from request to distinguish them from real revisions and attachments which have the same status.
	 *
	 * @param  \WP_Post $post                 Post object.
	 * @param string    $original_post_status Post status sent with request.
	 *
	 * @return bool
	 */
	private function is_auto_draft( \WP_Post $post, string $original_post_status ) {

		return
			! in_array( $post->post_status, [ 'inherit', 'revision' ], true )
			&& 'auto-draft' === $original_post_status;
	}

	/**
	 * Fetch data of original featured image.
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function featured_image_path( \WP_Post $post ) {

		if ( ! has_post_thumbnail( $post ) ) {
			return '';
		}

		$meta = wp_get_attachment_metadata( get_post_thumbnail_id( $post ) );

		$upload_dir = wp_upload_dir();

		return rtrim( $upload_dir['basedir'] ?? '' . '/' . $meta['file'] ?? '', '/' );
	}

	/**
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {

		return array_key_exists( $offset, $this->to_array() );
	}

	/**
	 * @param string $offset
	 *
	 * @return mixed|null
	 */
	public function offsetGet( $offset ) {

		$array = $this->to_array();

		return $array[ $offset ] ?? null;
	}

	/**
	 * Disabled.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {

		throw new \BadMethodCallException( sprintf( '%s is immutable.', __CLASS__ ) );
	}

	/**
	 * Disabled.
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {

		throw new \BadMethodCallException( sprintf( '%s is immutable.', __CLASS__ ) );
	}
}