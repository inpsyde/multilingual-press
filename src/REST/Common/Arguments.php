<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common;

/**
 * Interface for all arguments implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common
 * @since   3.0.0
 */
interface Arguments {

	/**
	 * Returns the arguments in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array[] Arguments array.
	 */
	public function to_array(): array;
}
