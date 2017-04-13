<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box UI implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBoxUI {

	/**
	 * Returns the ID of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string ID of the user interface.
	 */
	public function id(): string;

	/**
	 * Returns the name of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string Name of the user interface.
	 */
	public function name(): string;

	/**
	 * Initialize the UI. This will be called early to allow setup of early hooks like 'wp_ajax_*'.
	 *
	 * @return void
	 */
	public function initialize();

	/**
	 * Registers the updater of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_updater();

	/**
	 * Registers the view of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_view();
}
