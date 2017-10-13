<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all field access implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface Access {

	/**
	 * Returns the definition of all registered fields for the given resource.
	 *
	 * @see   \WP_REST_Controller::get_additional_fields
	 * @since 3.0.0
	 *
	 * @param string $resource Optional. Resource name (e.g., post). Defaults to empty string.
	 *
	 * @return array[] Field definitions.
	 */
	public function get_fields( string $resource = '' ): array;
}
