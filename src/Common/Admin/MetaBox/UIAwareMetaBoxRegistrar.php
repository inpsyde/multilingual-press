<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for UI-aware meta box registrar implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface UIAwareMetaBoxRegistrar extends MetaBoxRegistrar {

	/**
	 * Returns the ID of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string ID.
	 */
	public function id(): string;

	/**
	 * Sets the given user interface.
	 *
	 * @since 3.0.0
	 *
	 * @param MetaBoxUI $ui Meta box UI object.
	 *
	 * @return UIAwareMetaBoxRegistrar
	 */
	public function set_ui( MetaBoxUI $ui ): UIAwareMetaBoxRegistrar;
}
