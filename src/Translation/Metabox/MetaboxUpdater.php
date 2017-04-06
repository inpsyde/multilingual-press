<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Translation\Metabox;

use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;

/**
 * @package Inpsyde\MultilingualPress\Translation\Metabox
 * @since   3.0.0
 */
interface MetaboxUpdater {

	/**
	 * @param array $data
	 *
	 * @return MetaboxUpdater
	 */
	public function with_data( array $data ): MetaboxUpdater;

	/**
	 * @param ServerRequest $request
	 *
	 * @return bool
	 */
	public function update( ServerRequest $request ): bool;

}