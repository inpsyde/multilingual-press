<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Field;

use Inpsyde\MultilingualPress\REST\Common\Field\ReadableField;
use Inpsyde\MultilingualPress\REST\Common\Field\Reader;
use Inpsyde\MultilingualPress\REST\Common\Field\SchemaAwareField;
use Inpsyde\MultilingualPress\REST\Common\Field\UpdatableField;
use Inpsyde\MultilingualPress\REST\Common\Field\Updater;
use Inpsyde\MultilingualPress\REST\Common\Schema;

/**
 * Implementation of a complete (i.e., readable, updatable and schema-aware) field.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Field
 * @since   3.0.0
 */
final class Field implements ReadableField, UpdatableField, SchemaAwareField {

	/**
	 * @var array
	 */
	private $definition;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name       Field name.
	 * @param array  $definition Optional. Field definition. Defaults to empty array.
	 */
	public function __construct( string $name, array $definition = [] ) {

		$this->name = $name;

		$this->definition = $definition;
	}

	/**
	 * Sets the callback for reading the field value to the according callback on the given field reader object.
	 *
	 * @since 3.0.0
	 *
	 * @param Reader $reader Optional. Field reader object. Defaults to null.
	 *
	 * @return ReadableField Field object.
	 */
	public function set_get_callback( Reader $reader = null ): ReadableField {

		$this->definition['get_callback'] = $reader ? [ $reader, 'get_value' ] : null;

		return $this;
	}

	/**
	 * Sets the schema callback in the options to the according callback on the given schema object.
	 *
	 * @since 3.0.0
	 *
	 * @param Schema $schema Optional. Schema object. Defaults to null.
	 *
	 * @return SchemaAwareField Field object.
	 */
	public function set_schema( Schema $schema = null ): SchemaAwareField {

		$this->definition['schema'] = $schema ? [ $schema, 'definition' ] : null;

		return $this;
	}

	/**
	 * Sets the callback for updating the field value to the according callback on the given field updater object.
	 *
	 * @since 3.0.0
	 *
	 * @param Updater $updater Optional. Field updater object. Defaults to null.
	 *
	 * @return UpdatableField Field object.
	 */
	public function set_update_callback( Updater $updater = null ): UpdatableField {

		$this->definition['update_callback'] = $updater ? [ $updater, 'update_value' ] : null;

		return $this;
	}

	/**
	 * Returns the field definition (i.e., callbacks and schema).
	 *
	 * @see   register_rest_field()
	 * @since 3.0.0
	 *
	 * @return array Field definition.
	 */
	public function definition(): array {

		return $this->definition;
	}

	/**
	 * Returns the name of the field.
	 *
	 * @see   register_rest_field()
	 * @since 3.0.0
	 *
	 * @return string Field name.
	 */
	public function name(): string {

		return $this->name;
	}
}
