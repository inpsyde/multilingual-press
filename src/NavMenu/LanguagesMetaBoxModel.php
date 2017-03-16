<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\NavMenu;

use Inpsyde\MultilingualPress\Common\Admin\MetaBoxModel;

/**
 * Languages meta box model.
 *
 * @package Inpsyde\MultilingualPress\NavMenu
 * @since   3.0.0
 */
final class LanguagesMetaBoxModel implements MetaBoxModel {

	/**
	 * Returns the meta box ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box ID.
	 */
	public function id(): string {

		return 'mlp-languages';
	}

	/**
	 * Returns the meta box title.
	 *
	 * @since 3.0.0
	 *
	 * @return string Meta box title.
	 */
	public function title(): string {

		return __( 'Languages', 'multilingual-press' );
	}
}
