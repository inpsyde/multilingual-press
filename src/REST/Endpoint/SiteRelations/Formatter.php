<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Endpoint\SiteRelations;

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
	 * Returns a formatted representation of the given site relations data.
	 *
	 * @since 3.0.0
	 *
	 * @param int[]  $related_sites Array with site IDs as keys and related site IDs as values.
	 * @param string $context       Request context.
	 *
	 * @return array[] The formatted representation of the given data.
	 */
	public function format( array $related_sites, string $context ) {

		$site_ids = array_map( 'intval', array_keys( $related_sites ) );

		return array_reduce( $site_ids, function ( array $data, int $site_id ) use ( $related_sites, $context ) {

			$data[] = $this->format_item( $site_id, $related_sites[ $site_id ], $context );

			return $data;
		}, [] );
	}

	/**
	 * Filters the given response data and adds relevant links.
	 *
	 * @param int    $site_id       Site ID.
	 * @param int[]  $related_sites An array of related site IDs.
	 * @param string $context       Request context.
	 *
	 * @return array The filtered response data with links.
	 */
	private function format_item( int $site_id, array $related_sites, string $context ) {

		$data = [
			'site_id'       => $site_id,
			'related_sites' => array_map( 'intval', $related_sites ),
		];
		$data = $this->data_filter->filter_data( $data, $context );

		$response = $this->response_factory->create( [ $data ] );
		$response->add_links( [
			'self'       => [
				'href' => rest_url( "{$this->object_type}/{$site_id}" ),
			],
			'collection' => [
				'href' => rest_url( $this->object_type ),
			],
		] );

		return $this->data_access->get_data( $response );
	}
}
