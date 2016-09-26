<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service;

/**
 * Interface for all bootstrappable service provider implementations to be used for dependency management.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
interface BootstrappableServiceProvider extends ServiceProvider {

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container );
}
