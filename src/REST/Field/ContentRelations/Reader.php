<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Field\ContentRelations;

use Inpsyde\MultilingualPress\API\ContentRelations as API;
use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\REST\Common\Field;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;
use Inpsyde\MultilingualPress\Translation\Term\ActiveTaxonomies;

/**
 * Content relations field reader.
 *
 * @package Inpsyde\MultilingualPress\REST\Field\ContentRelations
 * @since   3.0.0
 */
final class Reader implements Field\Reader {

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_CONTENT_ID = 'multilingualpress.rest.content_id';

	/**
	 * Filter name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FILTER_CONTENT_TYPE = 'multilingualpress.rest.content_type';

	/**
	 * @var ActivePostTypes
	 */
	private $active_post_types;

	/**
	 * @var ActiveTaxonomies
	 */
	private $active_taxonomies;

	/**
	 * @var API
	 */
	private $api;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param API              $api               Content relations API object.
	 * @param ActivePostTypes  $active_post_types Active post type storage object.
	 * @param ActiveTaxonomies $active_taxonomies Active taxonomy storage object.
	 */
	public function __construct( API $api, ActivePostTypes $active_post_types, ActiveTaxonomies $active_taxonomies ) {

		$this->api = $api;

		$this->active_post_types = $active_post_types;

		$this->active_taxonomies = $active_taxonomies;
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
			$this->get_content_id( $object, $field_name, $request, $object_type ),
			$this->get_content_type( $object, $field_name, $request, $object_type )
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

	/**
	 * Returns the content ID for the given object.
	 *
	 * @param array            $object      Object data in array form.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Object type.
	 *
	 * @return int Content ID.
	 */
	private function get_content_id(
		array $object,
		string $field_name,
		\WP_REST_Request $request,
		string $object_type = ''
	): int {

		/**
		 * Filters the content ID of the object.
		 *
		 * @since 3.0.0
		 *
		 * @param int              $content_id  Content ID.
		 * @param array            $object      Object data in array form.
		 * @param string           $field_name  Field name.
		 * @param \WP_REST_Request $request     Request object.
		 * @param string           $object_type Object type.
		 */
		$content_id = (int) apply_filters(
			self::FILTER_CONTENT_ID,
			(int) ( $object['id'] ?? 0 ),
			$object,
			$field_name,
			$request,
			$object_type
		);

		return $content_id;
	}

	/**
	 * Returns the content type for the given object.
	 *
	 * @param array            $object      Object data in array form.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Object type.
	 *
	 * @return string Content type.
	 */
	private function get_content_type(
		array $object,
		string $field_name,
		\WP_REST_Request $request,
		string $object_type = ''
	): string {

		/**
		 * Filters the content type of the object.
		 *
		 * @since 3.0.0
		 *
		 * @param string           $content_type Content type.
		 * @param array            $object       Object data in array form.
		 * @param string           $field_name   Field name.
		 * @param \WP_REST_Request $request      Request object.
		 * @param string           $object_type  Object type.
		 */
		$content_id = (string) apply_filters(
			self::FILTER_CONTENT_TYPE,
			$this->get_content_type_from_object_type( $object_type ),
			$object,
			$field_name,
			$request,
			$object_type
		);

		return $content_id;
	}

	/**
	 * Returns the content type based on the given object type.
	 *
	 * @param string $object_type Object type.
	 *
	 * @return string Content type.
	 */
	private function get_content_type_from_object_type( string $object_type ): string {

		if ( post_type_exists( $object_type ) && $this->active_post_types->includes( $object_type ) ) {
			return ContentRelations::CONTENT_TYPE_POST;
		}

		if ( taxonomy_exists( $object_type ) && $this->active_taxonomies->includes( $object_type ) ) {
			return ContentRelations::CONTENT_TYPE_TERM;
		}

		return '';
	}
}
