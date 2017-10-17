<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Field\Posts\ContentRelations;

use Inpsyde\MultilingualPress\API\ContentRelations as API;
use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\REST\Common\Field;

final class Reader implements Field\Reader {

	/**
	 * @var API
	 */
	private $api;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param API $api Content relations API object.
	 */
	public function __construct( API $api ) {

		$this->api = $api;
	}

	/**
	 * Returns the value of the field with the given name of the given object.
	 *
	 * @since 3.0.0
	 *
	 * @param array            $object      Object data in array form.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return int[][] An array of arrays with site and content ID elements.
	 */
	public function get_value(
		array $object,
		string $field_name,
		\WP_REST_Request $request,
		string $object_type = ''
	) {

		$relations = $this->api->get_relations(
			get_current_blog_id(),
			$object['id'],
			ContentRelations::CONTENT_TYPE_POST
		);
		if ( ! $relations ) {
			return [];
		}

		$site_ids = array_keys( $relations );

		return array_reduce( $site_ids, function ( array $content_relations, int $site_id ) use ( $relations ) {

			$content_relations[] = [
				'site_id'    => $site_id,
				'content_id' => $relations[ $site_id ],
			];

			return $content_relations;
		}, [] );
	}
}
