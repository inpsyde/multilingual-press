<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress;

use BadMethodCallException;
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

	/**TODO: Adapt type to Module\ModuleServiceProvider.
	 * @var mixed[]
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
	 * @throws BadMethodCallException if called too early.
	 */
	public static function resolve( $name ) {

		if ( ! static::$container instanceof Container ) {
			throw new BadMethodCallException( sprintf(
				'Cannot resolve "%s". MultilingualPress has not yet been initialised.',
				$name
			) );
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

			// TODO: Take care of module service providers here...
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
	 * @throws BadMethodCallException if called on a MultilingualPress instance that has already been bootstrapped.
	 */
	public function bootstrap() {

		if ( $this->is_bootstrapped ) {
			throw new BadMethodCallException(
				'Cannot bootstrap a MultilingualPress instance that has already been bootstrapped.'
			);
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
		do_action( 'multilingualpress.bootstrap', $this );

		static::$container->lock();

		// TODO: Eventually remove the following block.
		class_exists( 'Mlp_Load_Controller' ) or require __DIR__ . '/inc/autoload/Mlp_Load_Controller.php';
		new \Mlp_Load_Controller( static::$container['multilingualpress.properties']->plugin_dir_path() . '/src/inc' );

		// TODO: Refactor according to new architecure.
		if ( ! ( new Temp\PreRunTester() )->test( static::$container ) ) {
			return false;
		}

		array_walk( $this->bootstrappables, function ( BootstrappableServiceProvider $provider ) {

			$provider->bootstrap( static::$container );
		} );

		unset( $this->bootstrappables );

		if ( $this->needs_modules() ) {
			/**
			 * Fires right before MultilingualPress registers any modules.
			 *
			 * @since 3.0.0
			 */
			do_action( 'multilingualpress.register_modules' );

			$this->register_modules();
		}

		unset( $this->modules );

		static::$container->bootstrap();

		$this->is_bootstrapped = true;

		// TODO: Remove as soon as the old front controller has been replaced completely.
		class_exists( 'Multilingual_Press' ) or require __DIR__ . '/inc/Multilingual_Press.php';

		// TODO: Refactor according to new architecure.
		return ( new \Multilingual_Press( static::$container ) )->setup();
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

		return array_key_exists( get_current_blog_id(), (array) get_site_option( 'inpsyde_multilingual', [] ) );
	}

	/**
	 * Registers all modules.
	 *
	 * @return void
	 */
	private function register_modules() {

		// Get module manager.

		// Check if instance of Module Manager interface. If not, throw RuntimeException.

		// TODO: Register modules...
	}
}
