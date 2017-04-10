<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box registrar implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBoxRegistrar {

	/**
	 * Registers meta boxes both for display and updating.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_meta_boxes();
}
