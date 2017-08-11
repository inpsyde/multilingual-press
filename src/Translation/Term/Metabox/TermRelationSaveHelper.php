<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox;

use Inpsyde\MultilingualPress\API\ContentRelations;

/**
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox
 * @since   3.0.0
 */
class TermRelationSaveHelper {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var SourceTermSaveContext
	 */
	private $save_context;

	/**
	 * @var array
	 */
	private $parent_ids;

	/**
	 * @var array
	 */
	private $connected_ids;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations      $content_relations
	 * @param SourceTermSaveContext $save_context
	 */
	public function __construct( ContentRelations $content_relations, SourceTermSaveContext $save_context ) {

		$this->content_relations = $content_relations;
		$this->save_context      = $save_context;
	}

	/**
	 * @param int $remote_site_id
	 *
	 * @return int
	 */
	public function related_term_parent( int $remote_site_id ): int {

		if ( is_array( $this->parent_ids ) ) {
			return (int) ( $this->parent_ids[ $remote_site_id ] ?? 0 );
		}

		if ( ! is_taxonomy_hierarchical( SourceTermSaveContext::TAXONOMY ) ) {
			$this->parent_ids = [];

			return 0;
		}

		$parent = $this->save_context[ SourceTermSaveContext::TERM_PARENT ];
		if ( ! $parent ) {
			$this->parent_ids = [];

			return 0;
		}

		$source_site_id = $this->save_context[ SourceTermSaveContext::SITE_ID ];
		if ( $source_site_id === $remote_site_id ) {
			return (int) $parent;
		}

		$this->parent_ids = $this->content_relations->get_relations( $source_site_id, $parent, 'term' );

		return (int) $this->parent_ids[ $remote_site_id ] ?? 0;
	}

	/**
	 * Set the source id of the element.
	 *
	 * @param   int $remote_site_id ID of remote site
	 * @param   int $remote_term_id ID of remote term
	 *
	 * @return  bool
	 */
	public function link_element( int $remote_site_id, int $remote_term_id ): bool {

		$source_site_id = $this->save_context[ SourceTermSaveContext::SITE_ID ];
		if ( $source_site_id === $remote_site_id ) {
			return true;
		}

		return $this->content_relations->set_relation(
			$source_site_id,
			$remote_site_id,
			$this->save_context[ SourceTermSaveContext::TERM_ID ],
			$remote_term_id,
			'term'
		);
	}

	/**
	 * Unlink any term connected with source term for given remote site id.
	 *
	 * @param int $remote_site_id
	 *
	 * @return int
	 */
	public function unlink_element( int $remote_site_id ): int {

		if ( ! is_array( $this->connected_ids ) ) {
			$this->connected_ids = $this->content_relations->get_relations(
				$this->save_context[ SourceTermSaveContext::SITE_ID ],
				$this->save_context[ SourceTermSaveContext::TERM_ID ],
				'term'
			);
		}

		if ( ! array_key_exists( $remote_site_id, $this->connected_ids ) ) {
			return 0;
		}

		return $this->content_relations->delete_relation(
			$this->save_context[ SourceTermSaveContext::SITE_ID ],
			$remote_site_id,
			$this->save_context[ SourceTermSaveContext::TERM_ID ],
			$this->connected_ids[ $remote_site_id ],
			'term'
		);
	}
}
