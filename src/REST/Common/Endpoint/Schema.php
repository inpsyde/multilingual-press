<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Endpoint;

use Inpsyde\MultilingualPress\REST\Common;

/**
 * Interface for all response data schema implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Endpoint
 * @since   3.0.0
 */
interface Schema extends Common\Schema {

	/**
	 * Returns the properties of the schema.
	 *
	 * @since 3.0.0
	 *
	 * @return array Properties definition.
	 */
	public function properties(): array;

	/**
	 * Returns the title of the schema.
	 *
	 * @since 3.0.0
	 *
	 * @return string Title.
	 */
	public function title(): string;
}
