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
	 * @param ContentRelations      $content_relations
	 * @param ServerRequest         $server_request
	 * @param SourcePostSaveContext $save_context
	 */
	public function __construct(
		ContentRelations $content_relations,
		ServerRequest $server_request,
		SourcePostSaveContext $save_context
	) {

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;

		$this->save_context = $save_context;
	}

	/**
	 * Save the remote post. Runs in site context or remote post.
	 *
	 * @param \WP_Post $remote_post
	 * @param int      $remote_site_id
	 *
	 * @return \WP_Post
	 */
	public function update( \WP_Post $remote_post, int $remote_site_id ): \WP_Post {

		if (
			! in_array( $remote_site_id, $this->save_context[ SourcePostSaveContext::RELATED_BLOGS ] )
			|| ! site_exists( $remote_site_id )
		) {
			return new \WP_Post( new \stdClass() );
		}

		$sites_request_data = $this->server_request->body_value( AdvancedPostTranslatorFields::INPUT_NAME_BASE ) ?: [];
		if ( ! $sites_request_data || ! is_array( $sites_request_data ) ) {
			return new \WP_Post( new \stdClass() );
		}

		$request_data = $sites_request_data[ $remote_site_id ] ?? null;
		if ( ! $request_data || ! is_array( $request_data ) ) {
			return new \WP_Post( new \stdClass() );
		}

		$relation_helper = new PostRelationSaveHelper( $this->content_relations, $this->save_context );

		$post_array = $this->build_remote_post_array( $remote_post, $remote_site_id, $request_data, $relation_helper );

		$new_remote_post_id = $post_array ? (int) wp_insert_post( $post_array, false ) : 0;

		if ( 0 >= $new_remote_post_id ) {
			return new \WP_Post( new \stdClass() );
		}

		if ( ! $relation_helper->sync_linked_element( $remote_site_id, $new_remote_post_id ) ) {
			return new \WP_Post( new \stdClass() );
		}

		$remote_post = get_post( $new_remote_post_id );
		if ( ! $remote_post instanceof \WP_Post ) {
			return new \WP_Post( new \stdClass() );
		}

		if ( ! empty( $request_data[ AdvancedPostTranslatorFields::SYNC_THUMBNAIL ] ) ) {
			$relation_helper->sync_thumb( $remote_post, $remote_site_id );
		}

		$this->sync_remote_terms( $remote_post, $request_data );

		return $remote_post;
	}

	/**
	 * @param \WP_Post               $remote_post
	 * @param int                    $remote_site_id
	 * @param array                  $request_data
	 * @param PostRelationSaveHelper $relation_helper
	 *
	 * @return array
	 */
	private function build_remote_post_array(
		\WP_Post $remote_post,
		int $remote_site_id,
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

		$remote_post->post_parent = $relation_helper->get_related_post_parent( $remote_site_id );

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
	 * Update terms for each taxonomy.
	 *
	 * @param  \WP_Post $remote_post
	 * @param  array    $request_data
	 *
	 * @return bool True on complete success, false when there were errors.
	 */
	private function sync_remote_terms( \WP_Post $remote_post, array $request_data ): bool {

		$tax_data = array_key_exists( AdvancedPostTranslatorFields::TAXONOMY, $request_data )
			? (array) $request_data[ AdvancedPostTranslatorFields::TAXONOMY ]
			: [];

		$errors = 0;

		$taxonomies = get_object_taxonomies( $remote_post, 'objects' );

		foreach ( $taxonomies as $slug => $taxonomy_object ) {
			if ( current_user_can( $taxonomy_object->cap->assign_terms, $slug ) ) {
				if ( ! $this->sync_remote_taxonomy_terms( $remote_post, $slug, $tax_data ) ) {
					$errors ++;
				}
			}
		}

		return $errors === 0;
	}

	/**
	 * @param \WP_Post $remote_post
	 * @param string   $taxonomy
	 * @param array    $request_data
	 *
	 * @return bool
	 */
	private function sync_remote_taxonomy_terms( \WP_Post $remote_post, string $taxonomy, array $request_data ): bool {

		$term_ids = empty( $request_data[ $taxonomy ] )
			? []
			: array_filter( array_map( 'intval', array_filter( (array) $request_data[ $taxonomy ], 'is_numeric' ) ) );

		if ( $term_ids ) {
			return ! is_wp_error( wp_set_object_terms( $remote_post->ID, $term_ids, $taxonomy ) );
		}

		// When user unchecked all terms from UI but post has already some terms, let's remove them.

		$post_terms = get_the_terms( $remote_post, $taxonomy );

		$post_term_ids = is_array( $post_terms ) && $post_terms
			? array_column( $post_terms, 'term_id' )
			: [];

		return
			! $post_term_ids
			|| ! is_wp_error( wp_remove_object_terms( $remote_post->ID, $post_term_ids, $taxonomy ) );
	}
}
