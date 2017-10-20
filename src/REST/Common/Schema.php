<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common;

/**
 * Interface for all schema implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common
 * @since   3.0.0
 */
interface Schema {

	/**
	 * Returns the schema definition.
	 *
	 * @since 3.0.0
	 *
	 * @return array Schema definition.
	 */
	public function definition(): array;
}
