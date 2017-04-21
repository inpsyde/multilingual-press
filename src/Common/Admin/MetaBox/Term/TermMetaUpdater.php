<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term;

use Inpsyde\MultilingualPress\Common\Admin\MetaBox\MetadataUpdater;
use Inpsyde\MultilingualPress\Translation\Term\MetaBox\SourceTermSaveContext;

/**
 * Interface for all term meta updater implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox\Term
 * @since   3.0.0
 */
interface TermMetaUpdater extends MetadataUpdater {

	/**
	 * Returns an instance with the given post.
	 *
	 * @since 3.0.0
	 *
	 * @param SourceTermSaveContext $save_context Save context object to set.
	 *
	 * @return TermMetaUpdater
	 */
	public function with_term_save_context( SourceTermSaveContext $save_context ): TermMetaUpdater;
}
