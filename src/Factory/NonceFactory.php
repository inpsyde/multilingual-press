<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;

/**
 * Interface for all factory implementations for nonce objects.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
interface NonceFactory extends Factory {

	/**
	 * Fully qualified name of the base interface.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const BASE = Nonce::class;

	/**
	 * Fully qualified name of the default class.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const DEFAULT_CLASS = WPNonce::class;

	/**
	 * Returns a new nonce object, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return Nonce Nonce object.
	 */
	public function create( array $args = [], $class = '' );
}
