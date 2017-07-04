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
class SimplePostTranslatorUpdater {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var SourcePostSaveContext
	 */
	private $save_context;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

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
			return $this->create_empty_post();
		}

		$translate_for_sites = array_map( 'intval', (array) $this->server_request->body_value(
			SimplePostTranslatorFields::TRANSLATABLE_FIELD,
			INPUT_POST,
			FILTER_SANITIZE_NUMBER_INT,
			FILTER_REQUIRE_ARRAY
		) );

		$to_translate = in_array( $remote_site_id, $translate_for_sites, true );

		$relation_helper = new PostRelationSaveHelper( $this->content_relations, $this->save_context );

		$remote_post_parent = $remote_post->post_parent;

		$remote_post_id = (int) $remote_post->ID;

		$remote_post->post_parent = $relation_helper->get_related_post_parent( $remote_site_id );

		// TODO: Here is lots of stuff missing that was in the post translator before, see https://github.com/inpsyde/MultilingualPress/blob/60b1b30f6ba910d04f6f6cbc8a1a9717c02529f3/src/inc/post-translator/Mlp_Translatable_Post_Data.php#L102-L227.

		if ( $to_translate || $remote_post_parent !== $remote_post->post_parent ) {
			$remote_post_id = (int) wp_insert_post( $remote_post->to_array(), false );
			$remote_post = $remote_post_id ? get_post( $remote_post_id ) : $this->create_empty_post();
		}

		if ( 0 >= $remote_post_id ) {
			return $this->create_empty_post();
		}

		if ( $to_translate && ! $relation_helper->sync_linked_element( $remote_site_id, $remote_post_id ) ) {
			return $this->create_empty_post();
		}

		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$relation_helper->sync_thumb( $remote_post, $remote_site_id );
		}

		return $remote_post;
	}

	/**
	 * Returns a new empty post.
	 *
	 * @return \WP_Post
	 */
	private function create_empty_post() {

		return new \WP_Post( new \stdClass() );
	}
}
