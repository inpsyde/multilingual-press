<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Field;

use Inpsyde\MultilingualPress\REST\Common;

/**
 * Traversable field collection implementation using an array iterator.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Field
 * @since   3.0.0
 */
final class Collection implements Common\Field\Collection {

	/**
	 * @var Common\Field\Field[][]
	 */
	private $fields = [];

	/**
	 * Adds the given field object to the resource with the given name to the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string             $resource Resource name to add the field to.
	 * @param Common\Field\Field $field    Field object.
	 *
	 * @return Common\Field\Collection Collection object.
	 */
	public function add( string $resource, Common\Field\Field $field ): Common\Field\Collection {

		$this->fields[ $resource ][ $field->name() ] = $field;

		return $this;
	}

	/**
	 * Deletes the field object with the given name from the resource with the given name from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $resource   Resource name to delete the field from.
	 * @param string $field_name Field name.
	 *
	 * @return Common\Field\Collection Collection object.
	 */
	public function delete( string $resource, string $field_name ): Common\Field\Collection {

		unset( $this->fields[ $resource ][ $field_name ] );

		return $this;
	}

	/**
	 * Returns an iterator object for the internal fields array.
	 *
	 * @since 3.0.0
	 *
	 * @return \ArrayIterator Iterator object.
	 */
	public function getIterator() {

		return new \ArrayIterator( $this->fields );
	}
}
