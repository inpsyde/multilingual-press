<?php # -*- coding: utf-8 -*-

/**
 * This file incorporates work from Zend Framework "zend-diactoros" released under New BSD License
 * and covered by the following copyright and permission notices:
 *
 *      Copyright (c) Zend Technologies USA Inc. (http://www.zend.com)
 *
 * @see https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md
 * @see https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
 */

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\HTTP;

use Inpsyde\MultilingualPress\Common\Type\URL;

/**
 * URL implementation that is build starting from server data as array.
 *
 * @package Inpsyde\MultilingualPress\Common\HTTP
 * @since   3.0.0
 */
final class ServerURL implements URL {

	/**
	 * @var array
	 */
	private $server_data;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $host;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param array  $server_data Array of server data similar to $_SERVER
	 * @param string $host        Optional. If given force host to be taken from this value instead from server data.
	 *                            The string could just contain hostname or be in the format "hostname:port"
	 */
	public function __construct( array $server_data, string $host = '' ) {

		$this->server_data = $server_data;

		$this->host = $host;
	}

	/**
	 * Returns the URL string.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function __toString(): string {

		$this->ensure_url();

		return $this->url;
	}

	/**
	 * Extract URL from server data and stores in object properties if not set yet.
	 */
	private function ensure_url() {

		if ( null !== $this->url ) {
			return;
		}

		list( $host, $port ) = $this->marshal_host_and_port();

		if ( ! $host ) {
			$this->url = '';

			return;
		}

		list( $url_path, $fragment, $query ) = $this->marshal_path_fragment_and_query();

		$scheme = is_ssl() ? 'https' : 'http';

		$this->url = rtrim( "{$scheme}://$host", '/' );

		if ( $port && $port !== 80 ) {
			$this->url .= ":{$port}";
		}

		$this->url .= '/' . trim( $url_path, '/' );

		if ( $fragment ) {
			$this->url .= "#{$fragment}";
		}

		if ( $query ) {
			$this->url .= "?{$query}";
		}
	}

	/**
	 * @return array
	 */
	private function marshal_host_and_port(): array {

		if ( $this->host ) {

			$host = $this->host;
			$port = 80;

			if ( preg_match( '|\:(\d+)$|', $host, $matches ) ) {
				$host = substr( $host, 0, - 1 * ( strlen( $matches[1] ) + 1 ) );
				$port = (int) $matches[1];
			}

			return [ $host, $port ];
		}

		$server_name = $this->server_data['SERVER_NAME'] ?? null;

		if ( ! $server_name ) {
			return [ '', 80 ];
		}

		$host = $server_name;

		$server_port = $this->server_data['SERVER_PORT'] ?? null;

		$port = is_numeric( $server_port ) ? (int) $server_port : 80;

		$server_address = $this->server_data['SERVER_ADDR'] ?? null;

		if ( ! is_string( $server_address ) || ! preg_match( '/^\[[0-9a-fA-F\:]+\]$/', $host ) ) {
			return [ $host, $port ];
		}

		// Misinterpreted IPv6-Address reported for Safari on Windows
		if ( "{$port}]" === substr( "[{$server_address}]", strrpos( "[{$server_address}]", ':' ) + 1 ) ) {
			$port = 80;
		}

		return [ $host, $port ];
	}

	/**
	 * @return array
	 */
	private function marshal_path_fragment_and_query(): array {

		$url_path = $this->marshal_path();

		$query_pos = strpos( $url_path, '?' );
		if ( $query_pos !== false ) {
			$url_path = substr( $url_path, 0, $query_pos );
		}

		$fragment = '';
		if ( strpos( $url_path, '#' ) !== false ) {
			list( $url_path, $fragment ) = explode( '#', $url_path, 2 );
		}

		$query_string = $this->server_data['QUERY_STRING'] ?? null;

		$query = '';

		if ( is_string( $query_string ) && $query_string ) {
			$query = ltrim( $query_string, '?' );
		}

		return [ $url_path, $fragment, $query ];
	}

	/**
	 * @return string
	 */
	private function marshal_path(): string {

		// IIS7 with URL Rewrite: make sure we get the unencoded url
		$iis_url_rewritten = $this->server_data['IIS_WasUrlRewritten'] ?? null;
		$unencoded_url     = $this->server_data['UNENCODED_URL'] ?? null;

		if ( 1 === (int) $iis_url_rewritten && is_string( $unencoded_url ) && $unencoded_url ) {
			return $unencoded_url;
		}

		$request_uri        = $this->server_data['REQUEST_URI'] ?? null;
		$http_x_rewrite_url = $this->server_data['HTTP_X_REWRITE_URL'] ?? null;

		if ( $http_x_rewrite_url !== null ) {
			$request_uri = $http_x_rewrite_url;
		}

		// Check for IIS 7.0 or later with ISAPI_Rewrite
		$http_x_original_url = $this->server_data['HTTP_X_ORIGINAL_URL'] ?? null;
		if ( $http_x_original_url !== null ) {
			$request_uri = $http_x_original_url;
		}

		if ( is_string( $request_uri ) && $request_uri ) {
			return preg_replace( '#^[^/:]+://[^/]+#', '', $request_uri );
		}

		$orig_path_info = $this->server_data['ORIG_PATH_INFO'] ?? null;

		return is_string( $orig_path_info ) && $orig_path_info ? $orig_path_info : '/';
	}
}