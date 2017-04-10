<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all priority-aware meta box implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface PriorityAwareMetaBox extends MetaBox {

	/**
	 * Returns an instance with the given meta box priority.
	 *
	 * @since 3.0.0
	 *
	 * @param string $priority Meta box priority to set.
	 *
	 * @return PriorityAwareMetaBox
	 */
	public function with_priority( string $priority ): PriorityAwareMetaBox;

	/**
	 * Returns the meta box priority.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box priority.
	 */
	public function priority(): string;
}
