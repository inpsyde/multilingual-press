<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box UI implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
final class NullMetaBoxUI implements MetaBoxUI {

	/**
	 * Returns the ID of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string ID of the user interface.
	 */
	public function id(): string {

		return '';
	}

	/**
	 * Initializes the user interface.
	 *
	 * This will be called early to allow wiring up of early-running hooks, for example, 'wp_ajax_{$action}'.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function initialize() {

	}

	/**
	 * Returns the name of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return string Name of the user interface.
	 */
	public function name(): string {

		return '';
	}

	/**
	 * Registers the updater of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_updater() {

	}

	/**
	 * Registers the view of the user interface.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_view() {

	}
}
