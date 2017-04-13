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
	 * @return int
	 */
	public function get_related_post_parent(): int {

		$site_id = (int) get_current_blog_id();

		$source_post_id = $this->save_context[ SourcePostSaveContext::POST_ID ];

		if ( array_key_exists( $source_post_id, self::$parent_ids ) ) {

			return (int) self::$parent_ids[ $source_post_id ][ $site_id ] ?? 0;
		}

		$source_post   = get_post( $source_post_id );

		$source_parent = $source_post ? (int) $source_post->post_parent : 0;

		$source_site_id = $this->save_context[ SourcePostSaveContext::SITE_ID ];

		if ( $source_site_id === $site_id ) {
			self::$parent_ids[ $source_post_id ] = [];
		}

		if ( ! $source_parent ) {
			self::$parent_ids[ $source_post_id ] = [];

			return 0;
		}

		self::$parent_ids[ $source_post_id ] = $this->content_relations->get_relations(
			$this->save_context[ SourcePostSaveContext::SITE_ID ],
			$source_parent,
			'post'
		);

		return (int) self::$parent_ids[ $source_post_id ][ $site_id ] ?? 0;
	}

	/**
	 * set the source id of the element
	 *
	 * @param   int $remote_site_id ID of remote site
	 * @param   int $remote_post_id ID of remote post
	 *
	 * @return  bool
	 */
	public function update_linked_element( int $remote_site_id, int $remote_post_id ): bool {

		$source_post_id = $this->save_context[ SourcePostSaveContext::POST_ID ];
		$source_site_id = $this->save_context[ SourcePostSaveContext::SITE_ID ];

		return $this->content_relations->set_relation(
			$source_site_id,
			$remote_site_id,
			$source_post_id,
			$remote_post_id,
			'post'
		);
	}

}