<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

/**
 * Interface for all attachment copier implementations.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
interface AttachmentCopier {

	/**
	 * Copies all attachment files of the site with given ID to the current site.
	 *
	 * @since 3.0.0
	 *
	 * @param int $source_site_id Source site ID.
	 *
	 * @return bool Whether or not any attachment files were copied.
	 */
	public function copy_attachments( $source_site_id );
}
