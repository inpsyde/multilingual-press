<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox;

use Inpsyde\MultilingualPress\API\ContentRelations;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox
 * @since   3.0.0
 */
class PostRelationSaveHelper {

	private static $parent_ids = [];

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var SourcePostSaveContext
	 */
	private $save_context;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations      $content_relations
	 * @param SourcePostSaveContext $save_context
	 */
	public function __construct( ContentRelations $content_relations, SourcePostSaveContext $save_context ) {

		$this->content_relations = $content_relations;
		$this->save_context      = $save_context;
	}

	/**
	 * @param int $remote_site_id
	 *
	 * @return int
	 */
	public function get_related_post_parent( int $remote_site_id ): int {

		if ( is_array( self::$parent_ids ) ) {
			return (int) ( self::$parent_ids[ $remote_site_id ] ?? 0 );
		}

		$source_post_id = $this->save_context[ SourcePostSaveContext::POST_ID ];
		$source_site_id = $this->save_context[ SourcePostSaveContext::SITE_ID ];

		$source_post = $source_site_id === (int) get_current_blog_id()
			? get_post( $source_post_id )
			: get_blog_post( $source_site_id, $source_post_id );

		$source_parent = $source_post ? (int) $source_post->post_parent : 0;

		if ( ! $source_parent ) {
			self::$parent_ids = [];

			return 0;
		}

		if ( $source_site_id === $remote_site_id ) {
			return $source_parent;
		}

		self::$parent_ids = $this->content_relations->get_relations(
			$this->save_context[ SourcePostSaveContext::SITE_ID ],
			$source_parent,
			'post'
		);

		return (int) self::$parent_ids[ $remote_site_id ] ?? 0;
	}

	/**
	 * Set the source id of the element.
	 *
	 * @param   int $remote_site_id ID of remote site
	 * @param   int $remote_post_id ID of remote post
	 *
	 * @return  bool
	 */
	public function sync_linked_element( int $remote_site_id, int $remote_post_id ): bool {

		$source_post_id = $this->save_context[ SourcePostSaveContext::POST_ID ];
		$source_site_id = $this->save_context[ SourcePostSaveContext::SITE_ID ];

		if ( $source_site_id === $remote_site_id ) {
			return true;
		}

		return $this->content_relations->set_relation(
			$source_site_id,
			$remote_site_id,
			$source_post_id,
			$remote_post_id,
			'post'
		);
	}

	/**
	 * @param \WP_Post $remote_post
	 * @param int      $remote_site_id
	 *
	 * @return bool
	 */
	public function sync_thumb( \WP_Post $remote_post, int $remote_site_id ): bool {

		$source_site_id = (int) $this->save_context[ SourcePostSaveContext::SITE_ID ];

		if ( $source_site_id === $remote_site_id ) {
			return true;
		}

		$source_thumb_path = $this->save_context[ SourcePostSaveContext::FEATURED_IMG_PATH ];

		// There's no thumbnail on source post
		if ( empty( $source_thumb_path ) ) {
			return true;
		}

		$original_site = $this->maybe_switch_site( $remote_site_id );

		$upload_dir = wp_upload_dir();

		$upload_path = $upload_dir['path'] ?? '';
		$upload_url  = $upload_dir['url'] ?? '';

		if ( ! $upload_path || ! $upload_url || ! wp_mkdir_p( $upload_dir['path'] ) ) {

			$this->maybe_restore_site( $original_site );

			return false;
		}

		$filename = wp_unique_filename( $upload_path, basename( $source_thumb_path ) );

		if ( ! copy( $source_thumb_path, "{$upload_path}/{$filename}" ) ) {

			$this->maybe_restore_site( $original_site );

			return false;
		}

		$wp_filetype = wp_check_filetype( "{$upload_url}/{$filename}" );
		$attachment  = [
			'post_mime_type' => $wp_filetype['type'] ?? '',
			'guid'           => "{$upload_url}/{$filename}",
			'post_parent'    => $remote_post->ID,
			'post_title'     => '',
			'post_excerpt'   => '',
			'post_author'    => get_current_user_id(),
			'post_content'   => '',
		];

		include_once( ABSPATH . 'wp-admin/includes/image.php' ); //including the attachment function

		$full_path    = $upload_dir['path'] . '/' . $filename;
		$thumbnail_id = wp_insert_attachment( $attachment, $full_path );

		if ( is_wp_error( $thumbnail_id ) ) {
			return false;
		}

		wp_update_attachment_metadata(
			$thumbnail_id,
			wp_generate_attachment_metadata( $thumbnail_id, $full_path )
		);

		$result = (bool) update_post_meta( $remote_post->ID, '_thumbnail_id', $thumbnail_id );

		$this->maybe_restore_site( $original_site );

		return $result;
	}

	/**
	 * @param int $remote_site_id
	 *
	 * @return int
	 */
	private function maybe_switch_site( int $remote_site_id ): int {

		$current_site = (int) get_current_blog_id();

		if ( $remote_site_id !== $current_site ) {
			switch_to_blog( $remote_site_id );

			return $current_site;
		}

		return - 1;

	}

	/**
	 * @param int $original_site_id
	 *
	 * @return bool
	 */
	private function maybe_restore_site( int $original_site_id ): bool {

		if ( $original_site_id < 0 ) {
			return false;
		}

		restore_current_blog();

		$current_site = (int) get_current_blog_id();
		if ( $current_site !== $original_site_id ) {
			switch_to_blog( $original_site_id );
		}

		return true;
	}

}