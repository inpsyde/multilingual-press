<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Response;

/**
 * Interface for all response data filter implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Response
 * @since   3.0.0
 */
interface DataFilter {

	/**
	 * Returns the given data filtered according to the given context.
	 *
	 * @see   \WP_REST_Controller::filter_response_by_context
	 * @since 3.0.0
	 *
	 * @param array  $data    Unfiltered response data.
	 * @param string $context Optional. Context. Defaults to 'view'.
	 *
	 * @return array Filtered data.
	 */
	public function filter_data( array $data, string $context = 'view' ): array;
}
