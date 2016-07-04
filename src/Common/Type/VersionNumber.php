<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Interface for all version number data type implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @since   3.0.0
 */
interface VersionNumber {

	/**
	 * @since 3.0.0
	 *
	 * @var string Fallback version to be used in case validation failed.
	 */
	const FALLBACK_VERSION = '0.0.0';

	/**
	 * Returns the version string.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function __toString();
}
