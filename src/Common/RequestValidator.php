<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Interface for all request validator implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface RequestValidator {

	/**
	 * Checks if the request is valid.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $context Optional. Validation context. Defaults to null.
	 *
	 * @return bool Whether or not the request is valid.
	 */
	public function is_valid( $context = null );
}
