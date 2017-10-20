<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all field registry implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface Registry {

	/**
	 * Action name.
	 *
	 * When using this, pass the field collection object as first and only argument.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_REGISTER = 'multilingualpress.register_rest_fields';

	/**
	 * Registers the given fields.
	 *
	 * @since 3.0.0
	 *
	 * @param Collection $fields Field collection object.
	 *
	 * @return void
	 */
	public function register_fields( Collection $fields );
}
