<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Core\Exception\InstanceAlreadyBootstrapped;
use Inpsyde\MultilingualPress\Installation\SystemChecker;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\IntegrationServiceProvider;
use Inpsyde\MultilingualPress\Service\ServiceProvider;
use Inpsyde\MultilingualPress\Service\ServiceProviderCollection;

/**
 * MultilingualPress front controller.
 *
 * @package Inpsyde\MultilingualPress
 * @since   3.0.0
 */
final class MultilingualPress {

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_BOOTSTRAPPED = 'multilingualpress.bootstrapped';

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_REGISTER_MODULES = 'multilingualpress.register_modules';

	/**
	 * Option name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const OPTION_VERSION = 'mlp_version';

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var ServiceProviderCollection
	 */
	private $service_providers;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Container                 $container         Container object.
	 * @param ServiceProviderCollection $service_providers Service provider collection object.
	 */
	public function __construct( Container $container, ServiceProviderCollection $service_providers ) {

		$this->container = $container;

		$this->service_providers = $service_providers;
	}

	/**
	 * Bootstraps MultilingualPress.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether or not MultilingualPress was bootstrapped successfully.
	 *
	 * @throws InstanceAlreadyBootstrapped if called on a MultilingualPress instance that has already been bootstrapped.
	 */
	public function bootstrap(): bool {

		if ( did_action( self::ACTION_BOOTSTRAPPED ) ) {
			throw new InstanceAlreadyBootstrapped();
		}

		$this->service_providers->apply_method( 'register', $this->container );

		// Lock the container. Nothing can be registered after that.
		$this->container->lock();

		$this->service_providers->filter( function ( ServiceProvider $provider ) {

			return $provider instanceof IntegrationServiceProvider;
		} )->apply_method( 'integrate', $this->container );

		if ( ! $this->check_installation() ) {
			return false;
		}

		$this->service_providers->filter( function ( ServiceProvider $provider ) {

			return $provider instanceof BootstrappableServiceProvider;
		} )->apply_method( 'bootstrap', $this->container );

		$this->register_modules();

		$this->container->bootstrap();

		/**
		 * Fires right after MultilingualPress was bootstrapped.
		 *
		 * @since 3.0.0
		 */
		do_action( static::ACTION_BOOTSTRAPPED );

		return true;
	}

	/**
	 * Checks the current MultilingualPress installation.
	 *
	 * @return bool Whether or not MultilingualPress is installed properly.
	 */
	private function check_installation(): bool {

		$installation_check = $this->container['multilingualpress.installation_checker']->check();

		return SystemChecker::PLUGIN_DEACTIVATED !== $installation_check;
	}

	/**
	 * Checks if the current request needs MultilingualPress to register any modules.
	 *
	 * @return bool Whether or not MultilingualPress should register any modules.
	 */
	private function needs_modules(): bool {

		if ( is_network_admin() || in_array( $GLOBALS['pagenow'], [ 'admin-ajax.php', 'admin-post.php' ], true ) ) {
			return true;
		}

		return in_array(
			get_current_blog_id(),
			$this->container['multilingualpress.site_settings_repository']->get_site_ids(),
			true
		);
	}

	/**
	 * Registers all modules.
	 *
	 * @return void
	 */
	private function register_modules() {

		if ( ! $this->needs_modules() ) {
			return;
		}

		/**
		 * Fires right before MultilingualPress registers any modules.
		 *
		 * @since 3.0.0
		 */
		do_action( static::ACTION_REGISTER_MODULES );

		$activation = function ( ModuleServiceProvider $module, ModuleManager $module_manager, Container $container ) {

			$module->register_module( $module_manager ) && $module->activate( $container );
		};

		$this->service_providers->filter( function ( ServiceProvider $provider ) {

			return $provider instanceof ModuleServiceProvider;
		} )->apply_callback( $activation, $this->container['multilingualpress.module_manager'], $this->container );
	}
}
