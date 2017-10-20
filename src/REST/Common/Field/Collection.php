<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all field collection implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface Collection extends \IteratorAggregate {

	/**
	 * Adds the given field object to the resource with the given name to the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $resource Resource name to add the field to.
	 * @param Field  $field    Field object.
	 *
	 * @return Collection Collection object.
	 */
	public function add( string $resource, Field $field ): Collection;

	/**
	 * Deletes the field object with the given name from the resource with the given name from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $resource   Resource name to delete the field from.
	 * @param string $field_name Field name.
	 *
	 * @return Collection Collection object.
	 */
	public function delete( string $resource, string $field_name ): Collection;
}
