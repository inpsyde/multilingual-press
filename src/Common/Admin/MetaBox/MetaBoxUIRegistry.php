<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box UI registry implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBoxUIRegistry {

	/**
	 * Setup the registry so that meta boxes will be able to use registered UIs.
	 *
	 * @return void
	 */
	public function setup();

	/**
	 * @param MetaBoxUI $ui
	 *
	 * @return MetaBoxUIRegistry
	 */
	public function register_ui( MetaBoxUI $ui ): MetaBoxUIRegistry;

	/**
	 * @return string[]
	 */
	public function all_ui_names(): array;

	/**
	 * @return string[]
	 */
	public function all_ui_ids(): array;

	/**
	 * @return MetaBoxUI[]
	 */
	public function all_ui(): array;
}
