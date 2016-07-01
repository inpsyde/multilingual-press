<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Interface for all URL data type implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
interface URL {

	/**
	 * Returns the URL string.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function __toString();
}
