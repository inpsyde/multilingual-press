<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Assets;

use Inpsyde\MultilingualPress\Common\Type;

/**
 * Interface for all asset URL data type implementations, providing a file version.
 *
 * @package Inpsyde\MultilingualPress\Assets
 * @since   3.0.0
 */
interface URL extends Type\URL {

	/**
	 * Returns the file version.
	 *
	 * @since 3.0.0
	 *
	 * @return string File version.
	 */
	public function version();
}
