<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations;

use Inpsyde\MultilingualPress\Factory\RESTResponseFactory;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\Schema;
use Inpsyde\MultilingualPress\REST\Common\Response\DataAccess;
use Inpsyde\MultilingualPress\REST\Common\Response\DataFilter;

use function Inpsyde\MultilingualPress\rest_url;

class Formatter {

	/**
	 * @var DataAccess
	 */
	private $data_access;

	/**
	 * @var DataFilter
	 */
	private $data_filter;

	/**
	 * @var string
	 */
	private $object_type;

	/**
	 * @var RESTResponseFactory
	 */
	private $response_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param DataFilter          $data_filter      Response data filter object.
	 * @param Schema              $schema           Endpoint schema object.
	 * @param RESTResponseFactory $response_factory Response factory object.
	 * @param DataAccess          $data_access      Response data access object.
	 */
	public function __construct(
		DataFilter $data_filter,
		Schema $schema,
		RESTResponseFactory $response_factory,
		DataAccess $data_access
	) {

		$this->data_filter = $data_filter;

		$this->object_type = $schema->title();

		$this->response_factory = $response_factory;

		$this->data_access = $data_access;
	}

	/**
	 * Returns a formatted representation of the given content relations data.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]  $content_ids Array with site IDs as keys and content IDs as values.
	 * @param string $type        Content type.
	 * @param string $context     Request context.
	 *
	 * @return array[] The formatted representation of the given data.
	 */
	public function format( array $content_ids, string $type, string $context ) {

		return array_reduce( array_keys( $content_ids ), function ( array $data, int $site_id ) use (
			$content_ids,
			$type,
			$context
		) {

			$data[] = $this->format_item( $site_id, $content_ids[ $site_id ], $type, $context );

			return $data;
		}, [] );
	}

	/**
	 * Filters the given response data and adds relevant links.
	 *
	 * @param int    $site_id    Site ID.
	 * @param int    $content_id Content element ID.
	 * @param string $type       Content type.
	 * @param string $context    Request context.
	 *
	 * @return array The filtered response data with links.
	 */
	private function format_item(
		int $site_id,
		int $content_id,
		string $type,
		string $context
	) {

		$data = compact(
			'site_id',
			'content_id',
			'type'
		);
		$data = $this->data_filter->filter_data( $data, $context );

		$response = $this->response_factory->create( [ $data ] );
		$response->add_links( [
			'self'       => [
				'href' => rest_url( "{$this->object_type}/{$site_id}/{$content_id}/{$type}" ),
			],
			'collection' => [
				'href' => rest_url( $this->object_type ),
			],
		] );

		return $this->data_access->get_data( $response );
	}
}
