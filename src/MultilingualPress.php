<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Core\Exception\InstanceAlreadyBootstrapped;
use Inpsyde\MultilingualPress\Core\Exception\CannotResolveName;
use Inpsyde\MultilingualPress\Installation\SystemChecker;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

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
	 * @var Container
	 */
	private static $container;

	/**
	 * @var BootstrappableServiceProvider[]
	 */
	private $bootstrappables = [];

	/**
	 * @var bool
	 */
	private $is_bootstrapped = false;

	/**
	 * @var ModuleServiceProvider[]
	 */
	private $modules = [];

	/**@todo Migrate from old 'mlp_version' option.
	 * @var string
	 */
	private $version_option = 'multilingualpress_version';

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
	public static function resolve( $name ) {

		if ( ! static::$container instanceof Container ) {
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
	 * @return static MultilingualPress instance.
	 */
	public function register_service_provider( ServiceProvider $provider ) {

		$provider->register( static::$container );

		if ( $provider instanceof BootstrappableServiceProvider ) {
			$this->bootstrappables[] = $provider;

			if ( $provider instanceof ModuleServiceProvider ) {
				$this->modules[] = $provider;
			}
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
	public function bootstrap() {

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

		// TODO: Eventually remove the following block.
		class_exists( 'Mlp_Load_Controller' ) or require __DIR__ . '/inc/autoload/Mlp_Load_Controller.php';
		new \Mlp_Load_Controller( static::$container['multilingualpress.properties']->plugin_dir_path() . '/src/inc' );

		if ( ! $this->check_installation() ) {
			return false;
		}

		$this->bootstrap_service_providers();

		$needs_modules = $this->needs_modules();
		if ( $needs_modules ) {
			$this->register_modules();
		}

		unset( $this->modules );

		static::$container->bootstrap();

		$this->is_bootstrapped = true;

		/**
		 * Fires right after MultilingualPress was bootstrapped.
		 *
		 * @since 3.0.0
		 */
		do_action( static::ACTION_BOOTSTRAPPED );

		// TODO: Eventually remove/refactor according to new architecure as soon as the old controller got replaced.
		class_exists( 'Multilingual_Press' ) or require __DIR__ . '/inc/Multilingual_Press.php';
		$old_controller = new \Multilingual_Press();
		if ( $needs_modules ) {
			$old_controller->setup();
		}
		add_action( 'wp_loaded', [ $old_controller, 'prepare_plugin_data' ] );

		return true;
	}

	/**
	 * Checks (and adapts) the current MultilingualPress installation.
	 *
	 * @return bool Whether or not MultilingualPress is installed properly.
	 */
	private function check_installation() {

		$system_checker = static::$container['multilingualpress.system_checker'];

		$installation_check = $system_checker->check_installation();

		if ( SystemChecker::PLUGIN_DEACTIVATED === $installation_check ) {
			return false;
		}

		if ( SystemChecker::INSTALLATION_OK === $installation_check ) {
			$type_factory = static::$container['multilingualpress.type_factory'];

			$installed_version = $type_factory->create_version_number( [
				get_network_option( null, $this->version_option ),
			] );

			$current_version = $type_factory->create_version_number( [
				static::$container['multilingualpress.properties']->version(),
			] );

			switch ( $system_checker->check_version( $installed_version, $current_version ) ) {
				case SystemChecker::INSTALLATION_OK:
					return true;

				case SystemChecker::NEEDS_INSTALLATION:
					static::$container['multilingualpress.installer']->install();
					break;

				case SystemChecker::NEEDS_UPGRADE:
					static::$container['multilingualpress.network_plugin_deactivator']->deactivate_plugins( [
						'disable-acf.php',
						'mlp-wp-seo-compat.php',
					] );

					static::$container['multilingualpress.updater']->update( $installed_version );
					break;
			}

			update_network_option( null, $this->version_option, $current_version );
		}

		return true;
	}

	/**
	 * Bootstraps all registered bootstrappable service providers.
	 *
	 * @return void
	 */
	private function bootstrap_service_providers() {

		array_walk( $this->bootstrappables, function ( BootstrappableServiceProvider $provider ) {

			$provider->bootstrap( static::$container );
		} );

		unset( $this->bootstrappables );
	}

	/**
	 * Checks if the current request needs MultilingualPress to register any modules.
	 *
	 * @return bool Whether or not MultilingualPress should register any modules.
	 */
	private function needs_modules() {

		if ( is_network_admin() || in_array( $GLOBALS['pagenow'], [ 'admin-ajax.php', 'admin-post.php' ], true ) ) {
			return true;
		}

		return array_key_exists(
			get_current_blog_id(),
			// TODO: Don't hardcode the option, and also maybe even check some other way.
			(array) get_network_option( null, 'inpsyde_multilingual', [] )
		);
	}

	/**
	 * Registers all modules.
	 *
	 * @return void
	 */
	private function register_modules() {

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
}
