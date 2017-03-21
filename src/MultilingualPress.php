<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Core\Exception\InstanceAlreadyBootstrapped;
use Inpsyde\MultilingualPress\Core\Exception\CannotResolveName;
use Inpsyde\MultilingualPress\Installation;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;
use Inpsyde\MultilingualPress\Service\ServiceProviderHandling;

/**
 * MultilingualPress front controller.
 *
 * @package Inpsyde\MultilingualPress
 * @since   3.0.0
 */
final class MultilingualPress {

	use ServiceProviderHandling {
		register_service_provider as _register_service_provider;
	}

	/**
	 * Action name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ACTION_BOOTSTRAP = 'multilingualpress.bootstrap';

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
	const VERSION_OPTION = 'mlp_version';

	/**
	 * @var Container
	 */
	private static $container;

	/**
	 * @var bool
	 */
	private $is_bootstrapped = false;

	/**
	 * @var ModuleServiceProvider[]
	 */
	private $modules = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 */
	public function __construct( Container $container ) {

		if ( ! static::$container ) {
			static::$container = $container;
		}
	}

	/**
	 * Resolve a shared value or factory callback from the container.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value or factory callback.
	 *
	 * @return mixed The value or factory callback.
	 *
	 * @throws CannotResolveName if there is no container available (i.e., MultilingualPress has not been intitialised).
	 */
	public static function resolve( string $name ) {

		if ( ! static::$container ) {
			throw CannotResolveName::for_name( $name );
		}

		return static::$container[ $name ];
	}

	/**
	 * Registers the given service provider.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider Service provider object.
	 *
	 * @return MultilingualPress MultilingualPress instance.
	 */
	public function register_service_provider( ServiceProvider $provider ): MultilingualPress {

		// Call the (renamed) method provided by the service trait.
		$this->_register_service_provider( $provider );

		if ( $provider instanceof ModuleServiceProvider ) {
			$this->modules[] = $provider;
		}

		return $this;
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

		if ( $this->is_bootstrapped ) {
			throw new InstanceAlreadyBootstrapped();
		}

		/**
		 * Fires right before MultilingualPress gets bootstrapped.
		 *
		 * Hook here to register custom service providers.
		 *
		 * @since 3.0.0
		 *
		 * @param static $multilingualpress MultilingualPress instance.
		 */
		do_action( static::ACTION_BOOTSTRAP, $this );

		static::$container->lock();

		$this->integrate_service_providers();

		if ( ! $this->check_installation() ) {
			return false;
		}

		$this->bootstrap_service_providers();

		$this->register_modules();

		static::$container->bootstrap();

		$this->is_bootstrapped = true;

		/**
		 * Fires right after MultilingualPress was bootstrapped.
		 *
		 * @since 3.0.0
		 */
		do_action( static::ACTION_BOOTSTRAPPED );

		return true;
	}

	/**
	 * Checks (and adapts) the current MultilingualPress installation.
	 *
	 * @return bool Whether or not MultilingualPress is installed properly.
	 *
	 * @see Installation\InstallationChecker::check()             That triggers SystemChecker::ACTION_AFTER_CHECK action
	 * @see Installation\InstallationServiceProvider::bootstrap() That handle SystemChecker::ACTION_AFTER_CHECK action
	 */
	private function check_installation(): bool {

		$installation_check = static::$container['multilingualpress.installation_checker']->check();

		return Installation\SystemChecker::PLUGIN_DEACTIVATED !== $installation_check;
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
			static::$container['multilingualpress.site_settings_repository']->get_site_ids(),
			true
		);
	}

	/**
	 * Registers all modules.
	 *
	 * @return void
	 */
	private function register_modules() {

		if ( $this->needs_modules() ) {
			/**
			 * Fires right before MultilingualPress registers any modules.
			 *
			 * @since 3.0.0
			 */
			do_action( static::ACTION_REGISTER_MODULES );

			$module_manager = static::$container['multilingualpress.module_manager'];

			array_walk( $this->modules, function ( ModuleServiceProvider $module ) use ( $module_manager ) {

				if ( $module->register_module( $module_manager ) ) {
					if ( $module instanceof ActivationAwareModuleServiceProvider ) {

						$module->activate();
					}
				}
			} );
		}

		$this->modules = [];
	}
}
