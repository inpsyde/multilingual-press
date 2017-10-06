<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Cache\Driver\WPObjectCacheDriver;
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
	private $prefix = '';

	/**
	 * @var ClassResolver
	 */
	private $pool_class_resolver;

	/**
	 * Constructor. Sets the properties.
	 *
	 * @param string $prefix Prefix to prepend to all pull objects namespace.
	 * @param string $pool_default_class
	 */
	public function __construct( string $prefix, string $pool_default_class = WPCachePool::class ) {

		$this->pool_class_resolver = new ClassResolver( CachePool::class, $pool_default_class );
		$this->prefix              = $prefix;
	}

	/**
	 * @return string
	 */
	public function prefix(): string {

		return $this->prefix;
	}

	/**
	 * @param string           $namespace
	 * @param CacheDriver|null $driver
	 *
	 * @return CachePool
	 */
	public function create( string $namespace, CacheDriver $driver = null ): CachePool {

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class( $this->prefix . $namespace, $driver ?: new WPObjectCacheDriver() );
	}

	/**
	 * @param string           $namespace
	 * @param CacheDriver|null $driver
	 *
	 * @return CachePool
	 */
	public function create_for_network( string $namespace, CacheDriver $driver = null ): CachePool {

		$pool_class = $this->pool_class_resolver->resolve();

		if ( $driver && ! $driver->is_sidewide() ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Cannot create a network-wide cache pool baked by the site specific driver "%s".',
					get_class( $driver )
				)
			);
		}

		$driver or $driver = new WPObjectCacheDriver( CacheDriver::FOR_NETWORK );

		return new $pool_class( $this->prefix . $namespace, $driver );
	}

	/**
	 * @param string $namespace
	 *
	 * @return WPCachePool
	 */
	public function create_ethereal( string $namespace ) {

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class( $this->prefix . $namespace, new EphemeralCacheDriver() );
	}

	/**
	 * @param string $namespace
	 *
	 * @return CachePool
	 */
	public function create_ethereal_for_network( string $namespace ): CachePool {

		$pool_class = $this->pool_class_resolver->resolve();

		return new $pool_class( $this->prefix . $namespace, new EphemeralCacheDriver( CacheDriver::FOR_NETWORK ) );
	}

}