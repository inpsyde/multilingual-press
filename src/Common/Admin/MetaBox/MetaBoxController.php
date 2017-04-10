<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box controller implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBoxController {

	/**
	 * Returns the meta box (data) instance.
	 *
	 * @since 3.0.0
	 *
	 * @return MetaBox
	 */
	public function meta_box(): MetaBox;

	/**
	 * Returns the metadata updater instance for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return MetadataUpdater
	 */
	public function updater(): MetadataUpdater;

	/**
	 * Returns the view instance for the meta box.
	 *
	 * @since 3.0.0
	 *
	 * @return MetaBoxView
	 */
	public function view(): MetaBoxView;
}
