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

	/**@todo Discuss moving the context to the constructor (which is not included here, of course).
	 * Checks if the nonce is valid with respect to the given context.
	 *
	 * @since 3.0.0
	 *
	 * @param Context $context Optional. Nonce context object. Defaults to null.
	 *
	 * @return bool Whether or not the nonce is valid.
	 */
	public function is_valid( Context $context = null );
}
