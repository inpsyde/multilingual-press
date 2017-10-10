<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Cache\Driver\WPObjectCacheDriver;
use Inpsyde\MultilingualPress\Cache\Exception\InvalidCacheDriver;
use Inpsyde\MultilingualPress\Cache\Pool\CachePool;
use Inpsyde\MultilingualPress\Cache\Pool\WPCachePool;
use Inpsyde\MultilingualPress\Common\Factory\ClassResolver;

/**
 * A factory for Cache pool objects.
 *
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
class CacheFactory {

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @var ClassResolver
	 */
	private $pool_class_resolver;

	/**
	 * Constructor. Sets the properties.
	 *
	 * @param string $prefix             Prefix to prepend to all pull objects namespace.
	 * @param string $pool_default_class Default class name.
	 */
	public function __construct( string $prefix = '', string $pool_default_class = WPCachePool::class ) {

		$this->prefix = $prefix;

		$this->pool_class_resolver = new ClassResolver( CachePool::class, $pool_default_class );
	}

	/**
	 * @return string
	 */
	public function prefix(): string {

		return $this->prefix;
	}

	/**
	 * @param string           $namespace Cache pool namespace.
	 * @param CacheDriver|null $driver    Cache pool driver.
	 *
	 * @return CachePool
	 */
	public function create( string $namespace, CacheDriver $driver = null ): CachePool {

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class( $this->pool_namespace( $namespace ), $driver ?? new WPObjectCacheDriver() );
	}

	/**
	 * @param string           $namespace Cache pool namespace.
	 * @param CacheDriver|null $driver    Cache pool driver.
	 *
	 * @return CachePool
	 *
	 * @throws InvalidCacheDriver If a site-specific is used instead of a network one.
	 */
	public function create_for_network( string $namespace, CacheDriver $driver = null ): CachePool {

		if ( $driver && ! $driver->is_network() ) {
			throw InvalidCacheDriver::for_site_driver_as_network( $driver );
		}

		if ( ! $driver ) {
			$driver = new WPObjectCacheDriver( CacheDriver::FOR_NETWORK );
		}

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class( $this->pool_namespace( $namespace ), $driver );
	}

	/**
	 * @param string $namespace Ethereal cache pool namespace.
	 *
	 * @return WPCachePool
	 */
	public function create_ethereal( string $namespace ) {

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class( $this->pool_namespace( $namespace ), new EphemeralCacheDriver() );
	}

	/**
	 * @param string $namespace Ethereal cache network pool namespace.
	 *
	 * @return CachePool
	 */
	public function create_ethereal_for_network( string $namespace ): CachePool {

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class(
			$this->pool_namespace( $namespace ),
			new EphemeralCacheDriver( CacheDriver::FOR_NETWORK )
		);
	}

	/**
	 * @param string $namespace Pool namespace provided by user.
	 *
	 * @return string
	 */
	private function pool_namespace( string $namespace ): string {

		return $this->prefix() . $namespace;
	}

}
