<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations;

use Inpsyde\MultilingualPress\API\ContentRelations;

/**
 * Content relations API.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations
 * @since   3.0.0
 */
class API {

	/**
	 * @var ContentRelations
	 */
	private $api;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ContentRelations $api Content relations API object.
	 */
	public function __construct( ContentRelations $api ) {

		$this->api = $api;
	}

	/**
	 * Creates a relationship for the given content.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]  $content_ids Array with site IDs as keys and content IDs as values.
	 * @param string $type        Content type.
	 *
	 * @return int The relationship ID.
	 */
	public function create_relationship( array $content_ids, string $type ): int {

		$content_ids = array_map( 'intval', $content_ids );

		$relationship_id = $this->api->get_relationship_id( $content_ids, $type, true );
		if ( $relationship_id ) {
			foreach ( $content_ids as $site_id => $content_id ) {
				$this->api->set_relation( $relationship_id, $site_id, $content_id );
			}
		}

		return $relationship_id;
	}

	/**
	 * Returns the content IDs for the given relationship ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $relationship_id Relationship ID.
	 *
	 * @return int[] Array with site IDs as keys and content IDs as values.
	 */
	public function get_content_ids( int $relationship_id ): array {

		return $this->api->get_content_ids( $relationship_id );
	}

	/**
	 * Returns all relations for the given content element.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $site_id    Site ID.
	 * @param int    $content_id Content ID.
	 * @param string $type       Content type.
	 *
	 * @return int[] Array with site IDs as keys and content IDs as values.
	 */
	public function get_relations( int $site_id, int $content_id, string $type ): array {

		return $this->api->get_relations( $site_id, $content_id, $type );
	}
}
