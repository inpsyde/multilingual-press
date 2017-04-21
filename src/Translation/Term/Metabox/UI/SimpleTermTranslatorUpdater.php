<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\SourceTermSaveContext;

use function Inpsyde\MultilingualPress\site_exists;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\TermRelationSaveHelper;

/**
 * @package Inpsyde\MultilingualPress\Translation\Term\MetaBox\UI
 * @since   3.0.0
 */
class SimpleTermTranslatorUpdater {

	/**
	 * @var ContentRelations
	 */
	private $content_relations;

	/**
	 * @var ServerRequest
	 */
	private $server_request;

	/**
	 * @var SourceTermSaveContext
	 */
	private $save_context;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param ContentRelations      $content_relations
	 * @param ServerRequest         $server_request
	 * @param SourceTermSaveContext $save_context
	 */
	public function __construct(
		ContentRelations $content_relations,
		ServerRequest $server_request,
		SourceTermSaveContext $save_context
	) {

		$this->content_relations = $content_relations;

		$this->server_request = $server_request;

		$this->save_context = $save_context;
	}

	/**
	 * Save the remote term. Runs in site context or remote term.
	 *
	 * @param \WP_Term $remote_term
	 * @param int      $remote_site_id
	 *
	 * @return \WP_Term
	 */
	public function update( \WP_Term $remote_term, int $remote_site_id ): \WP_Term {

		if (
			! in_array( $remote_site_id, $this->save_context[ SourceTermSaveContext::RELATED_BLOGS ] )
			|| ! site_exists( $remote_site_id )
			|| ! taxonomy_exists( $remote_term->taxonomy )
		) {
			return new \WP_Term( new \stdClass() );
		}

		$relation_helper = new TermRelationSaveHelper( $this->content_relations, $this->save_context );

		$term_id_to_relate = $this->term_to_relate( $remote_site_id, $remote_term->taxonomy, $relation_helper );

		if ( $term_id_to_relate === - 1 ) {

			$relation_helper->unlink_element( $remote_site_id );

			return new \WP_Term( new \stdClass() );
		}

		if ( ! $term_id_to_relate ) {
			return new \WP_Term( new \stdClass() );
		}

		$new_remote_term = get_term( $term_id_to_relate, $remote_term->taxonomy );

		if ( ! $new_remote_term instanceof \WP_Term ) {
			return new \WP_Term( new \stdClass() );
		}

		if ( (int) $new_remote_term->term_id === (int) $remote_term->term_id ) {
			return $remote_term;
		}

		if ( $relation_helper->link_element( $remote_site_id, (int) $new_remote_term->term_id ) ) {
			return new \WP_Term( new \stdClass() );
		}

		return $new_remote_term;
	}

	/**
	 * @param int                    $remote_site_id
	 * @param string                 $taxonomy
	 * @param TermRelationSaveHelper $relations
	 *
	 * @return int
	 */
	private function term_to_relate( int $remote_site_id, string $taxonomy, TermRelationSaveHelper $relations ): int {

		// Let's see if for this remote site, we should create a new ter, relate an existing one or do nothing
		$operation_for_sites = $this->server_request->body_value(
			SimpleTermTranslatorFields::RELATED_TERM_OPERATION,
			INPUT_POST,
			FILTER_SANITIZE_STRING,
			FILTER_REQUIRE_ARRAY
		);

		$operation = ( $operation_for_sites[ $remote_site_id ] ?? '' );

		switch ( $operation ) {
			case SimpleTermTranslatorFields::RELATED_TERM_DO_CREATE:
				return $this->create_new_term( $remote_site_id, $taxonomy, $relations );
			case SimpleTermTranslatorFields::RELATED_TERM_DO_SELECT:
				return $this->get_existing_term( $remote_site_id, $taxonomy );
			default:
				return 0;
		}
	}

	/**
	 * @param int                    $remote_site_id
	 * @param string                 $taxonomy
	 * @param TermRelationSaveHelper $relations
	 *
	 * @return int
	 */
	private function create_new_term( int $remote_site_id, string $taxonomy, TermRelationSaveHelper $relations ): int {

		// Let's see if for this remote site, user want to create a new term
		$create_terms_for_sites = (array) $this->server_request->body_value(
			SimpleTermTranslatorFields::RELATED_TERM_CREATE,
			INPUT_POST,
			FILTER_SANITIZE_STRING,
			FILTER_REQUIRE_ARRAY
		);

		$to_relate_name = trim( $create_terms_for_sites[ $remote_site_id ] ?? '' );

		// User neither not provided a name for a new term, return 0
		if ( ! $to_relate_name ) {
			return 0;
		}

		// Let's make sure provided term name doesn't exist already, to avoid duplicates, and return existing id if so
		$term_if_exists = get_term_by( 'name', $to_relate_name, $taxonomy );
		if ( $term_if_exists instanceof \WP_Term ) {
			return (int) $term_if_exists->term_id > 0 ? $term_if_exists->term_id : 0;
		}

		// Return 0 if get_term_by returned an error
		if ( is_wp_error( $term_if_exists ) ) {
			return 0;
		}

		// We are going to create a new term, let's see if we need to set a parent
		$parent = $relations->related_term_parent( $remote_site_id );

		// Create the term and then return the just created term id if creation was successful

		$insert = wp_insert_term( $to_relate_name, $taxonomy, compact( 'parent' ) );

		$inserted_id = is_array( $insert ) ? (int) ( $insert['term_id'] ?? 0 ) : 0;

		return $inserted_id > 0 ? $inserted_id : 0;
	}

	/**
	 * @param int    $remote_site_id
	 * @param string $taxonomy
	 *
	 * @return int
	 */
	private function get_existing_term( int $remote_site_id, string $taxonomy ): int {

		// Let's see if for this remote site, user want an existing term
		$existing_terms_for_sites = (array) $this->server_request->body_value(
			SimpleTermTranslatorFields::RELATED_TERM_SELECT,
			INPUT_POST,
			FILTER_SANITIZE_NUMBER_INT,
			FILTER_REQUIRE_ARRAY
		);

		$to_relate_id = (int) ( $existing_terms_for_sites[ $remote_site_id ] ?? 0 );

		// If an existing term is selected, but the term does not exists (e.g. deleted in meantime) return 0
		if ( $to_relate_id > 0 && ! term_exists( $to_relate_id, $taxonomy ) ) {
			return 0;
		}

		return $to_relate_id;
	}

}
