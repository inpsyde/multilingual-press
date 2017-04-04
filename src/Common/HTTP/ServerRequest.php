<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\HTTP;

/**
 * Interface for all HTTP server request abstraction implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\HTTP
 * @since   3.0.0
 */
interface ServerRequest extends Request {

	/**
	 * Returns a server value.
	 *
	 * @param string $name
	 *
	 * @return string Server setting value, empty string if not set.
	 */
	public function server_value( string $name ): string;
}
