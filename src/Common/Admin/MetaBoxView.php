<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Interface for all meta box view implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
interface MetaBoxView {

	/**
	 * Renders the HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param object $object Object.
	 * @param array  $args   Arguments.
	 *
	 * @return void
	 */
	public function render( $object, array $args );
}
