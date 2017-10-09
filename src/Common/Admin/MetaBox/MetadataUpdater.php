<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Admin\MetaBox;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;

/**
 * Interface for all metadata updater implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin\MetaBox
 * @since   3.0.0
 */
interface MetadataUpdater {

	/**
	 * Returns an instance with the given data.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to be set.
	 *
	 * @return MetadataUpdater
	 */
	public function with_data( array $data ): MetadataUpdater;

	/**
	 * Updates the metadata included in the given server request.
	 *
	 * @since 3.0.0
	 *
	 * @param ServerRequest $request Server request object.
	 *
	 * @return bool Whether or not the metadata was updated successfully.
	 */
	public function update( ServerRequest $request ): bool;
}
