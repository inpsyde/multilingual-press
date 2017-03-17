<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common;

/**
 * Interface for all array differ implementations.
 *
 * @package Inpsyde\MultilingualPress\Common
 * @since   3.0.0
 */
interface ArrayDiffer {

	/**
	 * Compares the given arrays and returns a new array holding the differences only.
	 *
	 * @since 3.0.0
	 *
	 * @param array $a An array of values.
	 * @param array $b Another array of values.
	 *
	 * @return array The array holding the differences only.
	 */
	public function diff( array $a, array $b ): array;
}
