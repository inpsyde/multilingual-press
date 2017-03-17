<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use WP_Error;

/**
 * Interface for all factory implementations for WordPress error objects.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
interface ErrorFactory extends Factory {

	/**
	 * Fully qualified name of the base class.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const BASE = WP_Error::class;

	/**
	 * Fully qualified name of the default class.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const DEFAULT_CLASS = self::BASE;

	/**
	 * Returns a new WordPress error object, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return WP_Error WordPress error object.
	 */
	public function create( array $args = [], string $class = '' ): WP_Error;
}
