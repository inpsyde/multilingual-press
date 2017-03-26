<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service;

/**
 * Trait for handling service providers.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
trait ServiceProviderHandling {

	/**
	 * @var BootstrappableServiceProvider[]
	 */
	private $bootstrappables = [];

	/**
	 * @var IntegrationServiceProvider[]
	 */
	private $integrations = [];

	/**
	 * Registers the given service provider.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider Service provider object.
	 *
	 * @return mixed Current instance.
	 */
	public function register_service_provider( ServiceProvider $provider ) {

		$provider->register( $this->container );

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
	private function integrate_service_providers( bool $unset = true ) {

		array_walk( $this->integrations, function ( IntegrationServiceProvider $provider ) {

			$provider->integrate( $this->container );
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
	private function bootstrap_service_providers( bool $unset = true ) {

		array_walk( $this->bootstrappables, function ( BootstrappableServiceProvider $provider ) {

			$provider->bootstrap( $this->container );
		} );

		if ( $unset ) {
			$this->bootstrappables = [];
		}
	}
}
