<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service;

use Inpsyde\MultilingualPress\Service\Exception\ContainerNotSet;
use ReflectionProperty;

/**
 * Trait for handling service providers.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
trait ServiceProviderHandling {

	/**
	 * @var Container
	 */
	private $_container;

	/**
	 * @var BootstrappableServiceProvider[]
	 */
	private $bootstrappables = [];

	/**
	 * @var IntegrationServiceProvider[]
	 */
	private $integrations = [];

	/**
	 * Sets the container to be used.
	 *
	 * This is necessary to enable usage of the trait regardless of how/where the container is managed by the object.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function set_container( Container $container ) {

		$this->_container = $container;
	}

	/**
	 * Registers the given service provider.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider Service provider object.
	 *
	 * @return static Current instance.
	 */
	public function register_service_provider( ServiceProvider $provider ) {

		$this->ensure_container( 'register' );

		$provider->register( $this->_container );

		if ( $provider instanceof IntegrationServiceProvider ) {
			$this->integrations[] = $provider;
		}

		if ( $provider instanceof BootstrappableServiceProvider ) {
			$this->bootstrappables[] = $provider;
		}

		return $this;
	}

	/**
	 * Integrates all third-party services that need to run early.
	 *
	 * @param bool $unset Optional. Unset the service providers when done? Defaults to true.
	 *
	 * @return void
	 */
	private function integrate_service_providers( $unset = true ) {

		array_walk( $this->integrations, function ( IntegrationServiceProvider $provider ) {

			$provider->integrate( $this->_container );
		} );

		if ( $unset ) {
			$this->integrations = [];
		}
	}

	/**
	 * Bootstraps all registered bootstrappable service providers.
	 *
	 * @param bool $unset Optional. Unset the service providers when done? Defaults to true.
	 *
	 * @return void
	 */
	private function bootstrap_service_providers( $unset = true ) {

		array_walk( $this->bootstrappables, function ( BootstrappableServiceProvider $provider ) {

			$provider->bootstrap( $this->_container );
		} );

		if ( $unset ) {
			$this->bootstrappables = [];
		}
	}

	/**
	 * Ensures a valid container object.
	 *
	 * If there is no container set, try to get it from a property with the name "container".
	 *
	 * @param string $action Optional. Action to be performed. Defaults to 'register'.
	 *
	 * @return void
	 *
	 * @throws ContainerNotSet if there is no container available.
	 */
	private function ensure_container( $action = 'register' ) {

		if ( $this->_container instanceof Container ) {
			return;
		}

		if ( property_exists( $this, 'container' ) ) {
			$container = ( new ReflectionProperty( __CLASS__, 'container' ) );
			$container->setAccessible( true );

			$container = $container->getValue( $this );
			if ( $container instanceof Container ) {
				$this->set_container( $container );

				return;
			}
		}

		throw ContainerNotSet::for_action( $action );
	}
}
