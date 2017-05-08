<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetaBoxView;

/**
 * Interface for all term meta box view implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term
 * @since   3.0.0
 */
interface TermMetaBoxView extends MetaBoxView {

	/**
	 * Returns an instance with the given term.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term $source_term Term object to set.
	 *
	 * @return TermMetaBoxView
	 */
	public function with_term( \WP_Term $source_term ): TermMetaBoxView;
}
