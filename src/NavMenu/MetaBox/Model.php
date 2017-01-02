<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\NavMenu\MetaBox;

use Inpsyde\MultilingualPress\Common\Admin\MetaBoxModel;

/**
 * Language meta box model.
 *
 * @package Inpsyde\MultilingualPress\NavMenu\MetaBox
 * @since   3.0.0
 */
final class Model implements MetaBoxModel {

	/**
	 * Returns the meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box ID.
	 */
	public function id() {

		return 'mlp-languages';
	}

	/**
	 * Returns the meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box title.
	 */
	public function title() {

		return __( 'Languages', 'multilingual-press' );
	}
}
