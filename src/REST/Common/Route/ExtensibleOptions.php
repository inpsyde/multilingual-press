<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Route;

use Inpsyde\MultilingualPress\REST\Common\Arguments;

/**
 * Interface for all implementations of extensible route options.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Route
 * @since   3.0.0
 */
interface ExtensibleOptions extends Arguments {

	/**
	 * Adds the given route options as new entry to the internal options.
	 *
	 * @since 3.0.0
	 *
	 * @param array $options Route options.
	 *
	 * @return ExtensibleOptions Options object.
	 */
	public function add( array $options ): ExtensibleOptions;
}
