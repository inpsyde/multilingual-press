<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Route;

use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\Schema;

/**
 * Interface for all implementations of schema-aware route options.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Route
 * @since   1.1.0
 */
interface SchemaAwareOptions extends Arguments {

	/**
	 * Sets the schema callback in the options to the according callback on the given schema object.
	 *
	 * @since 1.1.0
	 *
	 * @param Schema $schema Schema object.
	 *
	 * @return SchemaAwareOptions Options object.
	 */
	public function set_schema( Schema $schema ): SchemaAwareOptions;
}
