<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

/**
 * Interface for all meta box view implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetaBoxView {

	/**
	 * Returns an instance with the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to be set.
	 *
	 * @return MetaBoxView
	 */
	public function with_data( array $data ): MetaBoxView;

	/**
	 * Returns the rendered HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string Rendered HTML.
	 */
	public function render(): string;
}
