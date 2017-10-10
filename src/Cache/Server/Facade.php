<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Server;

/**
 * @package Inpsyde\MultilingualPress\Cache\Server
 * @since   3.0.0
 */
class Facade {

	/**
	 * @var Server
	 */
	private $server;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * Constructor.
	 *
	 * @param Server $server    Cache server to offer facade for.
	 * @param string $namespace Cache pool namespace to offer facade for.
	 */
	public function __construct( Server $server, string $namespace ) {

		$this->server    = $server;
		$this->namespace = $namespace;
	}

	/**
	 * Wrapper for server get.
	 *
	 * @param string $key Cache item key.
	 *
	 * @return mixed
	 *
	 * @see Server::claim()
	 */
	public function get( string $key ) {

		return $this->server->claim( $this->namespace, $key );
	}

	/**
	 * Wrapper for server flush.
	 *
	 * @param string $key Cache item key.
	 *
	 * @return int
	 *
	 * @see Server::flush()
	 */
	public function flush( string $key = null ): int {

		return $this->server->flush( $this->namespace, $key );
	}

}
