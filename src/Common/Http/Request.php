<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Http;

/**
 * Interface for all HTTP request abstraction implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\AcceptHeader
 * @since   3.0.0
 */
interface Request {

	/**
	 * Return a value from request body, optionally filtered.
	 *
	 * @param string $name
	 * @param int    $method
	 * @param int    $filter
	 * @param null   $options
	 *
	 * @return mixed
	 */
	public function body_value( string $name, int $method = INPUT_REQUEST, int $filter = FILTER_UNSAFE_RAW, $options = null );

	/**
	 * Returns header value as set in the request.
	 *
	 * @param string $name Header name
	 *
	 * @return string
	 */
	public function header( string $name ): string;

	/**
	 * Returns a parsed header value.
	 *
	 * @param string       $name  Header name.
	 * @param HeaderParser $parse Header parser to be used.
	 *
	 * @return array
	 */
	public function parsed_header( string $name, HeaderParser $parse ): array;

	/**
	 * Returns a server value.
	 *
	 * @param string $name
	 *
	 * @return string Server setting value, empty string if not set.
	 */
	public function server_value( string $name ): string;
}
