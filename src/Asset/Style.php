<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Asset;

/**
 * Interface for all style data type implementations.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
interface Style extends Asset {

	/**
	 * @param string $conditional
	 *
	 * @return Style Style instance.
	 */
	public function add_conditional( string $conditional ): Style;

	/**
	 * @return string
	 */
	public function media(): string;
}
