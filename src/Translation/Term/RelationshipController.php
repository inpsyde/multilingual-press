<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term;

use Inpsyde\MultilingualPress\API\ContentRelations;

/**
 * Relationship controller.
 *
 * @package Inpsyde\MultilingualPress\Translation\Term
 * @since   3.0.0
 */
class RelationshipController {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ContentRelations $content_relations Content relations API object.
	 */
	public function __construct( ContentRelations $content_relations ) {

		$this->content_relations = $content_relations;
	}

	/**
	 * Deletes the relation of the term with the given ID.
	 *
	 * @since   3.0.0
	 * @wp-hook delete_term
	 *
	 * @param int $term_id          Term ID.
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return bool Whether or not the term was handled successfully.
	 */
	public function handle_deleted_term(
		/** @noinspection PhpUnusedParameterInspection */
		$term_id,
		$term_taxonomy_id
	): bool {

		return $this->delete_relation( (int) get_current_blog_id(), (int) $term_taxonomy_id );
	}

	/**
	 * Deletes the relation for the given arguments.
	 *
	 * @param int $site_id          Site ID.
	 * @param int $term_taxonomy_id Term taxonomy ID.
	 *
	 * @return bool Whether or not the post was handled successfully.
	 */
	private function delete_relation( int $site_id, int $term_taxonomy_id ): bool {

		return $this->content_relations->delete_relation( [
			$site_id => $term_taxonomy_id,
		], ContentRelations::CONTENT_TYPE_TERM );
	}
}
