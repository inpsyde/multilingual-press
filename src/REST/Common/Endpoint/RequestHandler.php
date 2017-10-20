<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Endpoint;

/**
 * Interface for all request handler implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Endpoint
 * @since   3.0.0
 */
interface RequestHandler {

	/**
	 * Handles the given request object and returns the according response object.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function handle_request( \WP_REST_Request $request ): \WP_REST_Response;
}
