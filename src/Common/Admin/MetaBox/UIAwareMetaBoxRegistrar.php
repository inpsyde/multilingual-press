<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for UI aware meta box registrar implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface UIAwareMetaBoxRegistrar extends MetaBoxRegistrar {

	/**
	 * @return string
	 */
	public function identify_for_ui(): string;

	/**
	 * Tell the registrar to use given UI.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxUI $ui
	 *
	 * @return UIAwareMetaBoxRegistrar
	 */
	public function with_ui( MetaBoxUI $ui ): UIAwareMetaBoxRegistrar;
}
