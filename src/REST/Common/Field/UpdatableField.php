<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all implementations of updatable fields.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface UpdatableField extends Field {

	/**
	 * Sets the callback for updating the field value to the according callback on the given field updater object.
	 *
	 * @since 3.0.0
	 *
	 * @param Updater $updater Optional. Field updater object. Defaults to null.
	 *
	 * @return UpdatableField Field object.
	 */
	public function set_update_callback( Updater $updater = null ): UpdatableField;
}
