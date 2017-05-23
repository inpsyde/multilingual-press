<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\CustomPostTypeSupport;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Common\Setting\SettingsBoxView;
use Inpsyde\MultilingualPress\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Translation\Post\ActivePostTypes;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\CustomPostTypeSupport
 * @since   3.0.0
 */
final class ServiceProvider implements ModuleServiceProvider {

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
				$container['multilingualpress.update_post_type_support_settings_nonce']
			);
		};

		$container['multilingualpress.post_type_support_settings_updater'] = function ( Container $container ) {

			return new PostTypeSupportSettingsUpdater(
				$container['multilingualpress.post_type_repository'],
				$container['multilingualpress.update_post_type_support_settings_nonce']
			);
		};

		$container['multilingualpress.update_post_type_support_settings_nonce'] = function () {

			return new WPNonce( 'update_post_type_support_settings' );
		};
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registered successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ): bool {

		return $module_manager->register_module( new Module( 'custom_post_type_support', [
			'description' => __(
				'Enable translation of custom post types. Creates a second settings box below this. The post types must be activated for the whole network or on the main site.',
				'multilingualpress'
			),
			'name'        => __( 'Custom Post Type Support', 'multilingualpress' ),
			'active'      => false,
		] ) );
	}

	/**
	 * Performs various tasks on module activation.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function activate_module( Container $container ) {

		$repository = $container['multilingualpress.post_type_repository'];

		$settings_box = $container['multilingualpress.post_type_support_settings_box'];

		add_action( 'multilingualpress.after_module_list', function () use ( $settings_box, $repository ) {

			if ( $repository->get_custom_post_types() ) {
				( new SettingsBoxView( $settings_box ) )->render();
			}
		} );

		$updater = $container['multilingualpress.post_type_support_settings_updater'];
		add_action( 'multilingualpress.save_modules', [ $updater, 'update_settings' ] );

		add_filter( ActivePostTypes::FILTER_ACTIVE_POST_TYPES, function ( array $post_types ) use ( $repository ) {

			return array_merge( $post_types, $repository->get_supported_post_types() );
		} );

		$url_filter = $container['multilingualpress.post_type_link_url_filter'];
		add_action( 'multilingualpress.generate_permalink', [ $url_filter, 'enable' ] );
		add_action( 'multilingualpress.generated_permalink', [ $url_filter, 'disable' ] );
	}
}
