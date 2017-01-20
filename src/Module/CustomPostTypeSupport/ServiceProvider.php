<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\CustomPostTypeSupport;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\SettingsBoxView;
use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ActivationAwareness;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\CustomPostTypeSupport
 * @since   3.0.0
 */
final class ServiceProvider implements ActivationAwareModuleServiceProvider {

	use ActivationAwareness;

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container['multilingualpress.post_type_link_url_filter'] = function ( Container $container ) {

			return new URLFilter(
				$container['multilingualpress.post_type_repository']
			);
		};

		$container->share( 'multilingualpress.post_type_repository', function () {

			return new TypeSafePostTypeRepository();
		} );

		$container['multilingualpress.post_type_support_settings_box'] = function ( Container $container ) {

			return new CustomPostTypeSupportSettingsBox(
				$container['multilingualpress.post_type_repository'],
				$container['multilingualpress.post_type_support_settings_nonce']
			);
		};

		$container['multilingualpress.post_type_support_settings_nonce'] = function () {

			return new WPNonce( 'update_custom_post_type_support_settings' );
		};

		$container['multilingualpress.post_type_support_settings_updater'] = function ( Container $container ) {

			return new PostTypeSupportSettingsUpdater(
				$container['multilingualpress.post_type_repository'],
				$container['multilingualpress.post_type_support_settings_nonce']
			);
		};
	}

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		$this->on_activation( function () use ( $container ) {

			$repository = $container['multilingualpress.post_type_repository'];

			$settings_box = $container['multilingualpress.post_type_support_settings_box'];

			add_action( 'multilingualpress.after_module_list', function () use ( $settings_box, $repository ) {

				if ( $repository->get_custom_post_types() ) {
					( new SettingsBoxView( $settings_box ) )->render();
				}
			} );

			$updater = $container['multilingualpress.post_type_support_settings_updater'];
			add_action( 'multilingualpress.save_modules', [ $updater, 'update_settings' ] );

			add_filter( 'mlp_allowed_post_types', function ( array $post_types ) use ( $repository ) {

				return array_merge( $post_types, $repository->get_supported_post_types() );
			} );

			$url_filter = $container['multilingualpress.post_type_link_url_filter'];
			add_action( 'multilingualpress.generate_permalink', [ $url_filter, 'enable' ] );
			add_action( 'multilingualpress.generated_permalink', [ $url_filter, 'disable' ] );
		} );
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registerd successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ) {

		return $module_manager->register_module( new Module( 'custom_post_type_support', [
			'description' => __(
				'Enable translation of custom post types. Creates a second settings box below this. The post types must be activated for the whole network or on the main site.',
				'multilingual-press'
			),
			'name'        => __( 'Custom Post Type Support', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}
