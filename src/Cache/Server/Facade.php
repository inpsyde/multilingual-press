<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Server;

/**
 * @package MultilingualPress
 * @license http://opensource.org/licenses/MIT MIT
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
	 * @param Server $server
	 * @param string $namespace
	 */
	public function __construct( Server $server, string $namespace ) {

		$this->server = $server;
		$this->namespace = $namespace;
	}

	/**
	 * Wrapper for server get.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @see Server::get();
	 */
	public function get( string $key ) {

		return $this->server->claim( $this->namespace, $key );
	}

	/**
	 * Wrapper for server flush.
	 *
	 * @param string $key
	 *
	 * @return int
	 *
	 * @see Server::flush();
	 */
	public function flush( string $key = null ): int {

		return $this->server->flush( $this->namespace, $key );
	}

}