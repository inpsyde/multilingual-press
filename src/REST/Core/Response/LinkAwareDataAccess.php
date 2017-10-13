<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Response;

use Inpsyde\MultilingualPress\REST\Common\Response\DataAccess;

/**
 * Response data access implementation aware of links.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Response
 * @since   3.0.0
 */
final class LinkAwareDataAccess implements DataAccess {

	/**
	 * Returns an array holding the data as well as the defined links of the given response object.
	 *
	 * @see   \WP_REST_Controller::prepare_response_for_collection
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Response $response Response object.
	 *
	 * @return array The array holding the data as well as the defined links of the given response object.
	 */
	public function get_data( \WP_REST_Response $response ): array {

		$data = (array) $response->get_data();

		$links = $this->get_links( $response );
		if ( $links ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Returns an array holding the defined links of the given response object.
	 *
	 * @param \WP_REST_Response $response Response object.
	 *
	 * @return array The array holding the defined links of the given response object.
	 */
	private function get_links( \WP_REST_Response $response ): array {

		$server = rest_get_server();

		foreach ( [ 'get_compact_response_links', 'get_response_links' ] as $method ) {
			if ( is_callable( [ $server, $method ] ) ) {
				return (array) ( [ $server, $method ] )( $response );
			}
		}

		return [];
	}
}
