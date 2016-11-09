<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Nonce;

/**
 * Interface for all nonce implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce
 * @since   3.0.0
 */
interface Nonce {

	/**
	 * Returns the nonce value.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce value.
	 */
	public function __toString();

	/**
	 * Returns the nonce action.
	 *
	 * @since 3.0.0
	 *
	 * @return string Nonce action.
	 */
	public function action();

	/**
	 * Checks if the nonce is valid with respect to the current context.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not the nonce is valid.
	 */
	public function is_valid();
}
