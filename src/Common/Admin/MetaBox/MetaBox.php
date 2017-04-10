<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBox {

	/**
	 * Screen property key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_SCREEN_BASE = 'screen-base';

	/**
	 * Screen property key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_SCREEN_ID = 'screen-id';

	/**
	 * Screen property key.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const KEY_SCREEN_PARENT = 'screen-parent';

	/**
	 * Allows the meta box for all given screens, based on their base.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$screens One or more screen objects, arrays or bases.
	 *
	 * @return MetaBox
	 */
	public function allow_for_screens_by_base( ...$screens ): MetaBox;

	/**
	 * Allows the meta box for all given screens, based on their ID.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$screens One or more screen objects, arrays or IDs.
	 *
	 * @return MetaBox
	 */
	public function allow_for_screens_by_id( ...$screens ): MetaBox;

	/**
	 * Allows the meta box for all given screens, based on their parent base.
	 *
	 * @since 3.0.0
	 *
	 * @param array ...$screens One or more screen objects, arrays or parent bases.
	 *
	 * @return MetaBox
	 */
	public function allow_for_screens_by_parent( ...$screens ): MetaBox;

	/**
	 * Returns an instance with the given meta box context.
	 *
	 * @since 3.0.0
	 *
	 * @param string $context Meta box context to set.
	 *
	 * @return MetaBox
	 */
	public function with_context( string $context ): MetaBox;

	/**
	 * Returns the meta box context.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box context.
	 */
	public function context(): string;

	/**
	 * Returns an instance with the given meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Meta box ID to set.
	 *
	 * @return MetaBox
	 */
	public function with_id( string $id ): MetaBox;

	/**
	 * Returns the meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box ID.
	 */
	public function id(): string;

	/**
	 * Checks if the meta box is allowed for the given screen.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Screen $screen Screen object to check against.
	 *
	 * @return bool Whether or not the meta box is allowed for the given screen.
	 */
	public function is_allowed_for_screen( \WP_Screen $screen ): bool;

	/**
	 * Returns an instance with the given meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @param string $title Meta box title to set.
	 *
	 * @return MetaBox
	 */
	public function with_title( string $title ): MetaBox;

	/**
	 * Returns the meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box title.
	 */
	public function title(): string;
}
