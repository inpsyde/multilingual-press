<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all site-aware meta box controller implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface SiteAwareMetaBoxController extends MetaBoxController {

	/**
	 * Returns the site ID for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return int Site ID.
	 */
	public function site_id(): int;
}
