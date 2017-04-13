<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\PostRelationSaveHelper;
use Inpsyde\MultilingualPress\Translation\Post\MetaBox\SourcePostSaveContext;

use function Inpsyde\MultilingualPress\site_exists;

/**
 * @package Inpsyde\MultilingualPress\Translation\Post\MetaBox\UI
 * @since   3.0.0
 */
class AdvancedPostTranslatorUpdater {

	/**
	 * @var \WP_Post
	 */
	private $source_post;

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * @var SourcePostSaveContext
	 */
	private $save_context;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param \WP_Post               $source_post
	 * @param ContentRelations       $content_relations
	 * @param ServerRequest          $server_request
	 * @param SourcePostSaveContext  $save_context
	 */
	public function __construct(
		\WP_Post $source_post,
		ContentRelations $content_relations,
		ServerRequest $server_request,
		SourcePostSaveContext $save_context
	) {

		$this->source_post = $source_post;

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;

		$this->save_context = $save_context;
	}

	/**
	 * Save the remote post. This run in remote post site context.
	 *
	 * @param \WP_Post $remote_post
	 * @param int      $remote_site_id
	 *
	 * @return bool
	 */
	public function update( \WP_Post $remote_post, int $remote_site_id ): bool {

		if (
			! in_array( $remote_site_id, $this->save_context[ SourcePostSaveContext::RELATED_BLOGS ] )
			|| ! site_exists( $remote_site_id )
		) {
			return false;
		}

		$request_data = (array) $this->server_request->body_value( PostTranslatorInputHelper::NAME_BASE ) ?: [];
		if ( ! $request_data ) {
			return false;
		}

		$relation_helper = new PostRelationSaveHelper( $this->content_relations, $this->save_context );

		$post_array = $this->build_remote_post_array( $remote_post, $request_data, $relation_helper );

		$new_id = $post_array ? (int) wp_insert_post( $post_array, false ) : 0;

		if ( 0 >= $new_id ) {
			return false;
		}

		if ( ! $relation_helper->update_linked_element( $remote_site_id, $new_id ) ) {
			return false;
		}

		$remote_post = get_post( $new_id );
		if ( ! $remote_post ) {
			return false;
		}

		$this->copy_thumb( $remote_post, $request_data );

		$this->set_remote_tax_terms( $remote_post, $request_data );

		return true;
	}

	/**
	 * @param \WP_Post               $remote_post
	 * @param array                  $request_data
	 * @param PostRelationSaveHelper $relation_helper
	 *
	 * @return array
	 */
	private function build_remote_post_array(
		\WP_Post $remote_post,
		array $request_data,
		PostRelationSaveHelper $relation_helper
	): array {

		$remote_post = $this->update_post_property(
			AdvancedPostTranslatorFields::POST_TITLE,
			'post_title',
			$remote_post,
			$request_data
		);

		$remote_post = $this->update_post_property(
			AdvancedPostTranslatorFields::POST_NAME,
			'post_name',
			$remote_post,
			$request_data
		);

		$remote_post = $this->update_post_property(
			AdvancedPostTranslatorFields::POST_CONTENT,
			'post_content',
			$remote_post,
			$request_data
		);

		$remote_post = $this->update_post_property(
			AdvancedPostTranslatorFields::POST_EXCERPT,
			'post_excerpt',
			$remote_post,
			$request_data
		);

		if ( ! $this->remote_post_has_values( $remote_post ) ) {
			return [];
		}

		$author = $this->server_request->body_value(
			'post_author_override',
			INPUT_REQUEST,
			FILTER_SANITIZE_NUMBER_INT
		);

		if ( is_numeric( $author ) && $author ) {
			$remote_post->post_author = $author;
		}

		$remote_post->post_parent = $relation_helper->get_related_post_parent();

		return $remote_post->to_array();
	}

	/**
	 * Update post property on remote post based on request data.
	 *
	 * @param string   $request_key
	 * @param string   $post_key
	 * @param \WP_Post $remote_post
	 * @param array    $request_data
	 *
	 * @return \WP_Post
	 */
	private function update_post_property(
		string $request_key,
		string $post_key,
		\WP_Post $remote_post,
		array $request_data
	): \WP_Post {

		if ( array_key_exists( $request_key, $request_data ) && is_string( $request_data[ $request_key ] ) ) {
			$remote_post->{$post_key} = $request_data[ $request_key ];
		}

		return $remote_post;
	}

	/**
	 * Check if there actually is content in the translation. Prevents creation of empty translation drafts.
	 *
	 * @param \WP_Post $remote_post
	 *
	 * @return bool
	 */
	private function remote_post_has_values( \WP_Post $remote_post ): bool {

		return
			$remote_post->post_status !== 'draft'
			|| ( post_type_supports( $remote_post->post_type, 'title' ) && trim( $remote_post->post_title ) )
			|| ( post_type_supports( $remote_post->post_type, 'editor' ) && trim( $remote_post->post_content ) )
			|| ( post_type_supports( $remote_post->post_type, 'excerpt' ) && trim( $remote_post->post_excerpt ) );
	}

	/**
	 * @param \WP_Post $remote_post
	 * @param array    $request_data
	 *
	 * @return bool
	 */
	private function copy_thumb( \WP_Post $remote_post, array $request_data ): bool {

		// We should not save the thumbnail
		if ( empty( $request_data[ AdvancedPostTranslatorFields::SYNC_THUMBNAIL ] ) ) {
			return true;
		}

		$source_thumb_path = $this->save_context[ SourcePostSaveContext::FEATURED_IMG_PATH ];

		// There's no thumbnail on source post
		if ( empty( $source_thumb_path ) ) {
			return true;
		}

		$upload_dir = wp_upload_dir();

		if ( ! wp_mkdir_p( $upload_dir['path'] ) ) {
			// Failed create dir
			return false;
		}

		$filename = wp_unique_filename( $upload_dir['path'], basename( $source_thumb_path ) );

		if ( ! copy( $source_thumb_path, $upload_dir['path'] . '/' . $filename ) ) {
			return false;
		}

		$wp_filetype = wp_check_filetype( $upload_dir['url'] . '/' . $filename );
		$attachment  = [
			'post_mime_type' => $wp_filetype['type'],
			'guid'           => $upload_dir['url'] . '/' . $filename,
			'post_parent'    => $remote_post->ID,
			'post_title'     => '',
			'post_excerpt'   => '',
			'post_author'    => get_current_user_id(),
			'post_content'   => '',
		];

		$full_path = $upload_dir['path'] . '/' . $filename;
		$attach_id = wp_insert_attachment( $attachment, $full_path );

		if ( is_wp_error( $attach_id ) ) {
			return false;
		}

		wp_update_attachment_metadata(
			$attach_id,
			wp_generate_attachment_metadata( $attach_id, $full_path )
		);

		return (bool) update_post_meta( $remote_post->ID, '_thumbnail_id', $attach_id );
	}

	/**
	 * Update terms for each taxonomy.
	 *
	 * @param  \WP_Post $remote_post
	 * @param  array    $request_data
	 *
	 * @return bool True on complete success, false when there were errors.
	 */
	private function set_remote_tax_terms( \WP_Post $remote_post, array $request_data ): bool {

		$tax_data = array_key_exists( AdvancedPostTranslatorFields::TAXONOMY, $request_data )
			? (array) $request_data[ AdvancedPostTranslatorFields::TAXONOMY ]
			: [];

		if ( ! $tax_data ) {
			return true;
		}

		$errors = 0;

		$taxonomies = get_object_taxonomies( $remote_post, 'objects' );

		foreach ( $taxonomies as $taxonomy => $properties ) {

			if ( ! current_user_can( $properties->cap->assign_terms, $taxonomy ) ) {
				continue;
			}

			$terms = [];

			$term_ids = empty( $tax_data[ $taxonomy ] ) ? [] : (array) $tax_data[ $taxonomy ];

			foreach ( $term_ids as $term_id ) {
				$terms[ $term_id ] = get_term_by( 'id', (int) $term_id, $taxonomy );
			}

			$to_save = array_keys( array_filter( $terms ) );

			if ( is_wp_error( wp_set_object_terms( $remote_post->ID, $to_save, $taxonomy ) ) ) {
				$errors ++;
			}
		}

		return $errors === 0;
	}

}
