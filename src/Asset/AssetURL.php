<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Asset;

use Inpsyde\MultilingualPress\Common\Type\URL;

/**
 * Interface for all asset URL data type implementations, providing a file version.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
interface AssetURL extends URL {

	/**
	 * Returns the file version.
	 *
	 * @since 3.0.0
	 *
	 * @return string File version.
	 */
	public function version();
}
